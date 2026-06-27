<!DOCTYPE html>
<html>

<head>
    <title>Invoice #{{ $order->id }}</title>
    <style>
        body {
            font-family: sans-serif;
        }

        .box {
            padding: 20px;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="box">

        <div class="title">Invoice #{{ $order->id }}</div>
        <p>Date: {{ $order->created_at }}</p>

        <hr>

        <h3>Items</h3>

        @foreach($order->orderItems as $item)
        <p>
            {{ $item->book->title }} × {{ $item->quantity }}
            - ${{ $item->price * $item->quantity }}
        </p>
        @endforeach

        <hr>

        <h3>Total: ${{ $order->total_amount }}</h3>

    </div>

</body>

</html>