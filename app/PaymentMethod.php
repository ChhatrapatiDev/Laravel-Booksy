<?php

namespace App;

enum PaymentMethod: string
{
    case COD = 'cod';
    case Card = 'card';
    case UPI = 'upi';
}
