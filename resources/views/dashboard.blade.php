<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

<div class="min-h-screen flex flex-col items-center justify-center p-4">

    <!-- Card Container -->
    <div class="bg-white shadow-lg rounded-lg w-full max-w-lg p-6 space-y-6">

        <!-- Welcome Header -->
        <h2 class="text-2xl font-bold text-gray-800 text-center">
            Welcome, {{ auth()->user()->name }}
        </h2>

        <!-- Session Message -->
        @if(session('message'))
            <p class="text-blue-600 text-center font-medium">{{ session('message') }}</p>
        @endif

        <!-- Subscription Status -->
        <div class="text-center space-y-2">
            @if(auth()->user()->subscribed('default'))
                <p class="text-green-600 font-semibold text-lg">You are subscribed!</p>

                <!-- Subscription Expiry -->
                <p class="text-gray-700">
                    Expiry Date: 
                    <span class="font-medium text-gray-900">
                        {{ optional(auth()->user()->subscription('default'))->ends_at?->format('Y-m-d') ?? 'Active Plan' }}
                    </span>
                </p>

                <!-- Billing Portal -->
                <a href="{{ route('billing') }}" class="inline-block bg-blue-500 text-white px-5 py-2 rounded hover:bg-blue-600 transition">
                    Manage Subscription (Stripe)
                </a>

                <!-- Cancel Subscription -->
                <form method="POST" action="{{ route('fake.cancel') }}" class="inline-block">
                    @csrf
                    <button type="submit" class="bg-red-500 text-white px-5 py-2 rounded hover:bg-red-600 transition">
                        Cancel Subscription
                    </button>
                </form>
            @else
                <p class="text-red-600 font-semibold text-lg">You are not subscribed.</p>

                <!-- Real Stripe Subscribe -->
                <a href="{{ route('subscribe') }}" class="inline-block bg-green-500 text-white px-5 py-2 rounded hover:bg-green-600 transition">
                    Subscribe Now (Stripe)
                </a>

                <!-- Local Test Subscribe -->
                <a href="{{ route('fake.subscribe') }}" class="inline-block bg-yellow-500 text-white px-5 py-2 rounded hover:bg-yellow-600 transition">
                    Subscribe (Test Locally)
                </a>
            @endif
        </div>

        <!-- Logout -->
        <form method="POST" action="{{ route('logout') }}" class="text-center mt-4">
            @csrf
            <button type="submit" class="bg-gray-700 text-white px-5 py-2 rounded hover:bg-gray-800 transition">
                Logout
            </button>
        </form>

    </div>

</div>

</body>
</html>