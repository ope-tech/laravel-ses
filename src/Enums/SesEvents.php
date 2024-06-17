<?php

namespace OpeTech\LaravelSes\Enums;

enum SesEvents: string
{
    case Bounce = 'Bounce';
    case Complaint = 'Complaint';
    case Open = 'Open';
    case Delivery = 'Delivery';
    // case Send = 'Send';
    case Click = 'Click';
    case Reject = 'Reject';

}
