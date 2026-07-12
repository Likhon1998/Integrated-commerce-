@extends('website.layout')
@section('content')
<div class="max-w-xl mx-auto px-4 py-16" x-data="trackOrder()">
    <h1 class="text-3xl font-extrabold text-center mb-2">Track Your Order</h1>
    <p class="text-gray-600 text-center mb-8">Enter your invoice number and phone to check status.</p>
    <div class="bg-white border rounded-2xl p-6 space-y-4 shadow-sm">
        <input x-model="invoice" type="text" placeholder="Invoice Number (e.g. WEB-1-2026-12345)" class="w-full border rounded-xl px-4 py-3">
        <input x-model="phone" type="text" placeholder="Phone Number" class="w-full border rounded-xl px-4 py-3">
        <button @click="track()" :disabled="loading" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 disabled:opacity-50">
            <span x-text="loading?'Checking...':'Track Order'"></span>
        </button>
        <div x-show="result" x-cloak class="mt-4 p-4 rounded-xl" :class="success?'bg-green-50 text-green-800':'bg-red-50 text-red-800'">
            <p x-text="message" class="font-medium"></p>
            <template x-if="success">
                <div class="mt-2 text-sm space-y-1">
                    <p>Status: <strong x-text="status"></strong></p>
                    <p>Date: <span x-text="date"></span></p>
                    <p>Total: $<span x-text="total"></span></p>
                </div>
            </template>
        </div>
    </div>
</div>
@push('scripts')
<script>
function trackOrder(){
    return {
        invoice:'', phone:'', loading:false, result:false, success:false,
        message:'', status:'', date:'', total:'',
        async track(){
            this.loading=true; this.result=false;
            const res=await fetch(@json(route('website.track.submit')),{
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'},
                body:JSON.stringify({invoice_no:this.invoice,phone:this.phone})
            });
            const data=await res.json();
            this.result=true; this.success=data.success; this.message=data.message||(data.success?'Order found!':'');
            if(data.success){this.status=data.status;this.date=data.date;this.total=data.total;}
            this.loading=false;
        }
    }
}
</script>
@endpush
@endsection
