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

    <div class="bg-white shadow-lg rounded-lg w-full max-w-2xl p-6 space-y-6">

        <h2 class="text-2xl font-bold text-gray-800 text-center">
            Welcome, {{ auth()->user()->name }}
        </h2>

        @if(session('message'))
            <p class="text-blue-600 text-center font-medium">{{ session('message') }}</p>
        @endif

        <div class="text-center space-y-4">
            @if(auth()->user()->subscribed('default'))
                <p class="text-green-600 font-semibold text-lg">You are subscribed!</p>

                @if(auth()->user()->subscription('default')->onTrial())
                    <p class="text-orange-500 font-medium">
                        Trial ends at: {{ auth()->user()->subscription('default')->trial_ends_at->format('Y-m-d') }}
                    </p>
                @endif

                <p class="text-gray-700">
                    Expiry Date: 
                    <span class="font-medium text-gray-900">
                        {{ optional(auth()->user()->subscription('default'))->ends_at?->format('Y-m-d') ?? 'Active Plan' }}
                    </span>
                </p>

                <div class="flex flex-wrap justify-center gap-3">
                    <a href="{{ route('premium.content') }}" class="bg-purple-500 text-white px-5 py-2 rounded hover:bg-purple-600 transition">
                        Access Premium Content
                    </a>

                    <a href="{{ route('billing') }}" class="bg-blue-500 text-white px-5 py-2 rounded hover:bg-blue-600 transition">
                        Manage Billing (Stripe)
                    </a>

                    <form method="POST" action="{{ route('fake.cancel') }}">
                        @csrf
                        <button type="submit" class="bg-red-500 text-white px-5 py-2 rounded hover:bg-red-600 transition">
                            Cancel Subscription
                        </button>
                    </form>
                </div>

                <div class="mt-8">
                    <h3 class="text-lg font-bold text-gray-800 mb-3 text-left border-b pb-2">Recent Invoices</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="text-gray-500">
                                    <th class="py-2">Date</th>
                                    <th class="py-2">Total</th>
                                    <th class="py-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(auth()->user()->invoices() as $invoice)
                                    <tr class="border-t">
                                        <td class="py-2">{{ $invoice->date()->toFormattedDateString() }}</td>
                                        <td class="py-2">{{ $invoice->total() }}</td>
                                        <td class="py-2">
                                            <a href="{{ route('download.invoice', $invoice->id) }}" class="text-blue-500 hover:underline">Download PDF</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            @else
                <p class="text-red-600 font-semibold text-lg">You are not subscribed.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <div class="border rounded-lg p-4 border-blue-200 bg-blue-50">
                        <h4 class="font-bold">Basic Plan</h4>
                        <p class="text-sm text-gray-600 mb-3">$1.00/Month</p>
                        <a href="{{ route('subscribe', 'basic') }}" class="block bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition text-sm">
                            Subscribe Basic
                        </a>
                    </div>

                    <div class="border rounded-lg p-4 border-purple-200 bg-purple-50">
                        <h4 class="font-bold">Pro Plan</h4>
                        <p class="text-sm text-gray-600 mb-3">$10.00/Month (7 Day Trial)</p>
                        <a href="{{ route('subscribe', 'pro') }}" class="block bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600 transition text-sm">
                            Subscribe Pro
                        </a>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t">
                    <a href="{{ route('fake.subscribe') }}" class="text-sm text-gray-500 hover:text-yellow-600 underline">
                        Activate Test Subscription (Local Only)
                    </a>
                </div>
            @endif
        </div>

        <form method="POST" action="{{ route('logout') }}" class="text-center mt-6 border-t pt-4">
            @csrf
            <button type="submit" class="text-gray-500 hover:text-gray-800 transition text-sm font-medium">
                Logout from Account
            </button>
        </form>

    </div>

</div>

</body>
</html>