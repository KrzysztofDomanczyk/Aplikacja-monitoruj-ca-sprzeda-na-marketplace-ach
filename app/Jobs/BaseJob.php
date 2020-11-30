<?php

namespace App\Jobs;

use App\Libraries\Allegro\AllegroProductsGetter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

abstract class BaseJob
{
    public function failed($exception)
    {
        $message = $exception->getMessage() . PHP_EOL . PHP_EOL  . $exception;
         Mail::raw($message, function ($message) {
            $message->from('braki@phu-szczepan.pl', 'Monitor dostępności');
            $message->subject('[BŁĄD]Monitor dostępności ' . Carbon::now()->format('Y-m-d H:i:s'));
            $message->to('it@phu-szczepan.pl');
        });
        DB::table('jobs')->truncate();
    }
}
