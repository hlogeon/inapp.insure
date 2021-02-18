<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\FlocktoryCashback;
use App\Models\CashbackCompany;

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
    protected $description = 'Pulls cashbacks from flocktory and creates\updates database entries';

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
        $activeCampaignIds = [];
        foreach ($campaigns as $key => $campaign) {
            $activeCampaignIds[] = $campaign['id'];
            $companyData = [
                'id' => $campaign['site']['id'], 'title' => $campaign['site']['title'],
                'domain' => $campaign['site']['domain'], 'logo' => $campaign['images']['logotype_exchange'],
            ];
            $company = $this->findOrCreateCompany($companyData);
            $cashback = $this->updateOrCreateCashback($campaign, $company);
        }
        $cashbacksToDelete = FlocktoryCashback::whereNotIn('id', $activeCampaignIds);
        $cashbacksToDelete->update('deletedAt', new \DateTime());
        var_dump($cashbacksToDelete->get());
        return 0;
    }

    private function updateOrCreateCashback($campaign, $company)
    {
        $existing = FlocktoryCashback::where('flocktory_id', $campaign['id'])->first();
        if ($existing) {
            $existing->sale = $campaign['texts']['sale'];
            $existing->siteinfo = $campaign['texts']['siteinfo'];
            $existing->conditions = $campaign['texts']['conditions'];
            $existing->agreement = $campaign['agreement'];
            return $existing->save();
        }
        $bonus = [
            'flocktory_id' => $campaign['id'],
            'cashback_company_id' => $company->id,
            'favorite' => false,
            'featured' => false,
            'popular' => false,
            'premium' => false,
            'activated' => false,
            'activation_url' => '/api/v1/bonus/accept?id=' . $campaign['id'],
            'sale' => $campaign['texts']['sale'],
            'conditions' => $campaign['texts']['conditions'],
            'siteinfo' => $campaign['texts']['siteinfo'],
            'agreement' => $campaign['agreement'],
        ];
        return FlocktoryCashback::create($bonus);
    }

    private function findOrCreateCompany($companyData)
    {
        $existingCompany = CashbackCompany::where('flocktory_id', $companyData['id'])->first();
        if ($existingCompany) {
            return $existingCompany;
        }
        return CashbackCompany::create([
            'flocktory_id' => $companyData['id'],
            'title' => $companyData['title'] ? $companyData['title'] : $companyData['domain'],
            'domain' => $companyData['domain'],
            'logo' => $companyData['logo'],
        ]);
    }
}
