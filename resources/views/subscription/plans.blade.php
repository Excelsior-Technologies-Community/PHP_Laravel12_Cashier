<!DOCTYPE html>
<html>
<head>
    <title>Choose Your Plan</title>
    <style>
        .plans-container {
            display: flex;
            gap: 20px;
            max-width: 1200px;
            margin: 50px auto;
            font-family: Arial, sans-serif;
        }
        .plan {
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .plan.pro {
            border-color: #ff6b6b;
            background: #fff5f5;
        }
        .plan.basic {
            border-color: #4ecdc4;
            background: #f0fafa;
        }
        .price {
            font-size: 36px;
            color: #333;
            margin: 20px 0;
        }
        .features {
            text-align: left;
            margin: 20px 0;
            list-style: none;
            padding: 0;
        }
        .features li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        button {
            background: #28a745;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #218838;
        }
        .badge {
            background: #ffc107;
            color: #333;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="plans-container">
        <div class="plan basic">
            <h2>Basic Plan</h2>
            <div class="price">$9.99<span>/month</span></div>
            <ul class="features">
                <li>✓ Basic Features</li>
                <li>✓ Email Support</li>
                <li>✓ 1 Project</li>
                <li>✓ 5GB Storage</li>
            </ul>
            <form action="{{ route('subscribe', 'basic') }}" method="GET">
                <button type="submit">Choose Basic</button>
            </form>
        </div>

        <div class="plan pro">
            <h2>Pro Plan <span class="badge">Popular</span></h2>
            <div class="price">$19.99<span>/month</span></div>
            <div style="color: #28a745; font-size: 14px;">7 days free trial</div>
            <ul class="features">
                <li>✓ All Basic Features</li>
                <li>✓ Priority Support 24/7</li>
                <li>✓ Unlimited Projects</li>
                <li>✓ 50GB Storage</li>
                <li>✓ Advanced Analytics</li>
                <li>✓ Team Collaboration</li>
            </ul>
            <form action="{{ route('subscribe', 'pro') }}" method="GET">
                <button type="submit">Start Free Trial</button>
            </form>
        </div>
    </div>
    <div style="text-align: center; margin-top: 20px;">
        <a href="{{ route('dashboard') }}">← Back to Dashboard</a>
    </div>
</body>
</html>