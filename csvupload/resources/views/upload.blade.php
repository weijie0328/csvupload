<!DOCTYPE html>
<html>
<body>

<h2>Upload CSV</h2>

@if(session('success'))
    <div style="color: green">{{ session('success') }}</div>
@endif

<form action="{{ route('csv.upload') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file" accept=".csv">
    <button type="submit">Upload CSV</button>
</form>

</body>
</html>
