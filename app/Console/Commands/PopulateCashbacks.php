<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\FlocktoryCashback;

class PopulateCashbacks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cashback:populate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Some command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $response = Http::get('https://client.flocktory.com/v2/exchange/campaigns', [
            'token' => env('FLOCKTORY_TOKEN', '36c7afaf0080ddbe1f6c5339045963af'),
            'site_id' => env('FLOCKTORY_SITE_ID', 3169),
            'email' => 'flocktory@inapp.insure',
        ])->json();
        $campaigns = $response['campaigns'];
        $bonuses = [];
        foreach ($campaigns as $key => $campaign) {
            $bonus = [
                'id' => $campaign['id'],
                'favorite' => false,
                'featured' => false,
                'popular' => false,
                'premium' => false,
                'activated' => false,
                'activation_url' => '/api/v1/bonus/accept?id=' . $campaign['id'],
                'logo' => $campaign['images']['logotype_exchange'],
                'site_title' => $campaign['site']['title'],
                'site_domain' => $campaign['site']['domain'],
                'sale' => $campaign['texts']['sale'],
                'conditions' => $campaign['texts']['conditions'],
                'siteinfo' => $campaign['texts']['siteinfo'],
                'agreement' => $campaign['agreement'],

            ];
            FlocktoryCashback::create($bonus);
            array_push($bonuses, $bonus);
        }
        return 0;
    }
}
