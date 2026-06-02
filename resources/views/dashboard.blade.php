<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: auto; }
        .card { background: #f9f9f9; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .status-active { color: green; font-weight: bold; }
        .status-inactive { color: red; font-weight: bold; }
        .menu { background: #333; color: white; padding: 15px; margin-bottom: 20px; }
        .menu a { color: white; margin: 0 15px; text-decoration: none; }
        .btn { display: inline-block; padding: 10px 20px; margin: 5px; border-radius: 5px; text-decoration: none; }
        .btn-primary { background: #007bff; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: #333; }
        .message { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="menu">
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <a href="{{ route('plans') }}">Subscription Plans</a>
        <a href="{{ route('invoices') }}">Invoices</a>
        @if(auth()->user()->subscribed())
            <a href="{{ route('billing') }}">Billing Portal</a>
            <a href="{{ route('premium.content') }}">Premium Content</a>
        @endif
        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
            @csrf
            <button type="submit" style="background: none; border: none; color: white; cursor: pointer;">Logout</button>
        </form>
    </div>

    <div class="container">
        <h2>Welcome, {{ auth()->user()->name }}!</h2>
        
        @if(session('success'))
            <div class="message success">{{ session('success') }}</div>
        @endif
        
        @if(session('error'))
            <div class="message error">{{ session('error') }}</div>
        @endif

        <div class="card">
            <h3>Subscription Status</h3>
            @if(auth()->user()->subscribed())
                <p class="status-active">✓ Active Subscription</p>
                <p><strong>Plan:</strong> {{ auth()->user()->plan }}</p>
                
                @if(auth()->user()->subscription('default')->onGracePeriod())
                    <p style="color: orange;">⚠️ Your subscription will end on {{ auth()->user()->subscription('default')->ends_at->format('M d, Y') }}</p>
                    <form action="{{ route('subscription.resume') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success">Resume Subscription</button>
                    </form>
                @else
                    <form action="{{ route('subscription.cancel') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger">Cancel Subscription</button>
                    </form>
                @endif
                
                <form action="{{ route('update.payment.method') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary">Update Payment Method</button>
                </form>
            @else
                <p class="status-inactive">✗ No Active Subscription</p>
                <a href="{{ route('plans') }}" class="btn btn-success">Subscribe Now</a>
            @endif
        </div>

        @if(!app()->environment('production'))
        <div class="card">
            <h3>Testing Tools (Development Only)</h3>
            <a href="{{ route('fake.subscribe') }}" class="btn btn-warning">Fake Subscribe (Test)</a>
            <form action="{{ route('fake.cancel') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-danger">Fake Cancel (Test)</button>
            </form>
        </div>
        @endif
    </div>
</body>
</html>