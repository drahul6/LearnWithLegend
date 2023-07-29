<?php

namespace v2\Models\Wallet;


use v2\Traits\HasStatus;
use v2\Traits\HasDetails;
use Filters\Traits\Filterable;
use v2\Filters\Filters\LoanFilter;
use v2\Models\Wallet\Classes\AccountManager;
use Illuminate\Database\Eloquent\Model as Eloquent;

class GeneratedTransaction extends Eloquent
{
    use HasStatus;
    use Filterable;
    use HasDetails;

    protected $fillable = [
        'details',    'status'
    ];

    protected $table = 'generated_transactions';
    protected $connection = 'wallet';


    public static $query_config = [
        'filter_class' => LoanFilter::class, //the filter class 
        'pass_mark' => 1,
        'name' => 'generated_transactions', //variable name for this
    ];

    public static $statuses_config = [
        'use' => 'name',  //can be name or hierachy e.g draft or 1
        'column' => 'status',
        'push_url' => 'accounts/push_gen',  //ulr to update changes
        'use_hierarchy' => false,
        'states' => [
            [
                'name' => 'draft', //name of status e.g completed
                'hierarchy' => 1, //the hierachy  int e.g 1
                'color' => 'warning',    //the color e.g warning
                'after_set' => null, // a function that will be called after setting this status
                'before_set' => null, // a function that will be called before setting this status
                'is_final' => false, // this status cannot be reversed
            ],

            [
                'name' => 'published', //name of status e.g completed
                'hierarchy' => 2, //the hierachy  int e.g 1
                'color' => 'success',    //the color e.g warning
                'after_set' => 'publish_journals', // a function that will be called after setting this status
                'before_set' => null, // a function that will be called before setting this status
                'is_final' => true, // this status cannot be reversed
            ],

        ],
    ];


    public function publish_journals()
    {   
        $details = $this->DetailsArray;
        $postable_journals = $details['postable_journals'];
        AccountManager::postJournal($postable_journals);
    }
}
