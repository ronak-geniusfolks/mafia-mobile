<form id="syncForm" action="{{ $sync_url }}" method="POST">
    @csrf
    <input type="hidden" name="customer_name" value="{{ $customer_name }}">
    <input type="hidden" name="customer_no" value="{{ $customer_no }}">
</form>

<script>
    document.getElementById('syncForm').submit();
</script>
