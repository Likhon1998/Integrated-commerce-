<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AiChatController extends Controller
{
    /**
     * Handle the AI Chat Request
     */
    public function ask(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500'
        ]);

        $user = Auth::user();
        $shopId = $user->shop_id;
        $userMessage = $request->message;
        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            return response()->json(['reply' => 'API Key is missing in your .env file. 😔']);
        }

        // =================================================================
        // STEP 1: Dynamically Fetch the Database Schema
        // =================================================================
        $schema = $this->getDynamicDatabaseSchema();

        // =================================================================
        // STEP 2: The "Smart Router" Prompt (Bulletproof Math & Dates)
        // =================================================================
        $routerPrompt = "You are the core intelligence engine for 'Nexa POS'.
        User Message: '{$userMessage}'
        
        Here is the dynamic database schema of the system:
        $schema
        
        INSTRUCTIONS:
        1. CONVERSATION: If the user is just saying hello or asking a non-data question, reply starting with EXACTLY the word 'CHAT:' followed by your friendly response.
        
        2. DATABASE QUERY: If the user asks for data (sales, staff, products, stock, exchanges, customers), write a raw MySQL SELECT query.
        
        3. STRICT RULES FOR SQL (CRITICAL): 
           - RETURN ONLY the raw SQL string. No markdown, no backticks, no talk.
           - TENANT ISOLATION (CRITICAL): If the table has a 'shop_id' column, you MUST include 'WHERE shop_id = {$shopId}'. For the 'shops' table, use 'WHERE id = {$shopId}'.
           - REVENUE LOGIC: When calculating total sales/revenue from the 'orders' table, YOU MUST STRICTLY ADD: AND status = 'completed' AND is_exchange_receipt = 0. Do NOT sum refunded, cancelled, or exchange orders.
           - BULLETPROOF TIMEZONE (XAMPP SAFE): The database is in UTC. For Bangladesh Time (+6), ALWAYS use DATE(DATE_ADD(created_at, INTERVAL 6 HOUR)) when comparing dates. Example: DATE(DATE_ADD(created_at, INTERVAL 6 HOUR)) = '2026-04-05' or CURDATE().
           - LIMIT 15 to avoid server overload.";

        // Call Gemini 2.5 Flash for the query/routing
        $aiResponse = $this->callGemini($apiKey, $routerPrompt);
        $cleanedResponse = trim(preg_replace('/^```sql|```|sql$/m', '', $aiResponse));

        // =================================================================
        // STEP 3: Handle Conversational Chat
        // =================================================================
        if (str_starts_with(strtoupper($cleanedResponse), 'CHAT:')) {
            $chatReply = trim(substr($cleanedResponse, 5));
            return response()->json(['reply' => $chatReply]);
        }

        // =================================================================
        // STEP 4: Handle Database Query & Logging
        // =================================================================
        $sqlQuery = stristr($cleanedResponse, 'SELECT'); 
        $sqlQuery = trim($sqlQuery);

        if (!$sqlQuery) {
            return response()->json(['reply' => 'Ami ekhon ei bishoye tottho dite parchi na. Please ask something related to your shop data. 🛒']);
        }

        if (!str_starts_with(strtoupper($sqlQuery), 'SELECT')) {
            Log::warning("AI attempted dangerous query: " . $sqlQuery);
            return response()->json(['reply' => 'Security Error: Action not allowed. 🚫']);
        }

        // 🚀 THE SPY TRACKER: Log the exact query to storage/logs/laravel.log
        Log::info("AI SQL Query Executed: " . $sqlQuery);

        // Execute SQL Safely
        try {
            $dbResult = DB::select($sqlQuery);
        } catch (\Exception $e) {
            Log::error("AI SQL Execution Failed: " . $e->getMessage() . " | Query: " . $sqlQuery);
            return response()->json(['reply' => 'Database ektu busy ache, ba query-te problem hoyeche. Ektu pore try korun. ⚙️']);
        }

        // =================================================================
        // STEP 5: Convert Raw Data to a BEAUTIFUL Human Response
        // =================================================================
        $dataJson = json_encode($dbResult);
        
        $humanPrompt = "You are 'Nexa AI', a highly engaging and friendly assistant for Nexa POS.
        User asked: '{$userMessage}'
        Database returned: {$dataJson}
        
        Task: Summarize this data to make it look BEAUTIFUL, professional, and easy to read.
        
        FORMATTING RULES (CRITICAL):
        1. EMOJIS: Use relevant emojis (📦, 💰, 📈, ✨, 🛒, 🚗, ⌚ etc.) to make it visually attractive.
        2. USE HTML TAGS: Your output is rendered directly in a browser. You MUST use <b>text</b> for bold, <ul><li>item</li></ul> for lists, and <br> for new lines. 
        3. NO MARKDOWN: DO NOT use asterisks (**) or hashes (#) for formatting. Only use HTML tags.
        4. STRUCTURE: 
           - A cheerful opening line.
           - The data beautifully formatted in a list or easy-to-read structure.
           - A polite, helpful closing line.
        5. LANGUAGE: Reply in Banglish if they asked in Banglish. If English, reply in English.
        6. EMPTY DATA: If the data is empty `[]`, say 'Kono record pawa jayni 😔'.
        7. NEVER show SQL code.";

        $finalReply = $this->callGemini($apiKey, $humanPrompt);

        return response()->json(['reply' => $finalReply ?: 'Data process korte somossa hoyeche 😔']);
    }

    /**
     * Dynamically scan the database and create a schema string for the AI.
     */
    private function getDynamicDatabaseSchema()
    {
        // 🔴 TABLES TO HIDE FROM AI (System, Spatie ACL, and HQ tables)
        $ignoredTables = [
            'migrations', 'password_reset_tokens', 'personal_access_tokens',
            'failed_jobs', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches',
            'permissions', 'roles', 'model_has_permissions', 'model_has_roles', 'role_has_permissions',
        ];

        $schemaString = "";
        
        // Get all table names
        $tables = array_map('current', DB::select('SHOW TABLES'));

        foreach ($tables as $table) {
            if (in_array($table, $ignoredTables)) {
                continue;
            }

            // Get columns and hide sensitive data
            $columns = Schema::getColumnListing($table);
            $columns = array_diff($columns, ['password', 'remember_token']);

            $schemaString .= "Table: {$table} (" . implode(', ', $columns) . ")\n";
        }

        return $schemaString;
    }

    /**
     * Securely call the Gemini 2.5 Flash API with 503 Retry Logic.
     */
    private function callGemini($apiKey, $prompt, $retries = 1)
    {
        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
            }

            // Retry logic if Google servers are overloaded
            if ($response->status() == 503 && $retries > 0) {
                sleep(1); 
                return $this->callGemini($apiKey, $prompt, $retries - 1);
            }
            
            Log::error('Gemini API Error: ' . $response->status() . ' - ' . $response->body());
            return '';
            
        } catch (\Exception $e) {
            Log::error('Gemini Connection Error: ' . $e->getMessage());
            return '';
        }
    }
}