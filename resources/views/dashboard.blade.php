<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>
<h2>Welcome, {{ auth()->user()->name }}</h2>

@if(auth()->user()->subscribed())
    <p style="color:green">You are subscribed!</p>
    <a href="{{ route('billing') }}">Manage Subscription</a>
@else
    <p style="color:red">You are not subscribed.</p>
    <a href="{{ route('subscribe') }}">Subscribe Now</a>
@endif

<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit">Logout</button>
</form>
</body>
</html>
