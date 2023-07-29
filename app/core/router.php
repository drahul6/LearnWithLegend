<?php

use FastRoute\RouteCollector;

return $dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) {




    $r->addRoute(['GET', 'POST'], '/verify[/{path:.+}]', VerificationController::class);
    // $r->addRoute(['GET', 'POST'], '/auto-match[/{path:.+}]', AutoMatchingController::class);


    $r->addRoute('GET', '', HomeController::class);

    $r->addRoute(['GET', 'POST'], '/[/{path:.+}]', HomeController::class);
    $r->addRoute(['GET', 'POST'], '/pg[/{path:.+}]', HomeController::class);
    $r->addRoute(['GET', 'POST'], '/support[/{path:.+}]', SupportController::class);

    $r->addRoute(['GET', 'POST'], '/user_doc_crud[/{path:.+}]', "crud/" . UserDocCrudController::class);
    $r->addRoute(['GET', 'POST'], '/auto-match[/{path:.+}]', AutoMatchingController::class);
    $r->addRoute(['GET', 'POST'], '/r[/{path:.+}]', ReferralController::class);
    $r->addRoute(['GET', 'POST'], '/shop[/{path:.+}]', shopController::class);
    $r->addRoute(['GET', 'POST'], '/settings[/{path:.+}]', SettingsController::class);
    $r->addRoute(['GET', 'POST'], '/genealogy[/{path:.+}]', GenealogyController::class);
    $r->addRoute(['GET', 'POST'], '/forgot-password[/{path:.+}]', forgotPasswordController::class);
    $r->addRoute(['GET', 'POST'], '/register[/{path:.+}]', RegisterController::class);
    $r->addRoute(['GET', 'POST'], '/home[/{path:.+}]', HomeController::class);
    $r->addRoute(['GET', 'POST'], '/user[/{path:.+}]', UserController::class);
    $r->addRoute(['GET', 'POST'], '/withdrawals[/{path:.+}]', WithdrawalsController::class);
    $r->addRoute(['GET', 'POST'], '/admin[/{path:.+}]', AdminController::class);
    $r->addRoute(['GET', 'POST'], '/admin-dashboard[/{path:.+}]', AdminController::class);

    $r->addRoute(['GET', 'POST'], '/login[/{path:.+}]', LoginController::class);
    $r->addRoute(['GET', 'POST'], '/admin-profile[/{path:.+}]', AdminProfileController::class);
    $r->addRoute(['GET', 'POST'], '/user-profile[/{path:.+}]', UserProfileController::class);
    $r->addRoute(['GET', 'POST'], '/admin_panel', [LoginController::class, 'adminLogindfghjkioiuy3hj8']);



    #wallets
    $r->addRoute(['GET', 'POST'], '/accounts[/{path:.+}]', "wallet/" . AccountsController::class);
    $r->addRoute(['GET', 'POST'], '/journals[/{path:.+}]', "wallet/" . JournalsController::class);
    $r->addRoute(['GET', 'POST'], '/trial-balance[/{path:.+}]', "wallet/" . TrialBalanceController::class);

    //support
    $r->addRoute(['GET', 'POST'], '/ticket_crud[/{path:.+}]', "crud/" . TicketCrudController::class);




    /*
    $r->addRoute('GET', '/conversions/{id:.+}', [HomeController::class, 'get_conversion']);
    $r->addRoute('GET', '/conversion', [HomeController::class, 'tests']);
    $r->addGroup('/admin', function (RouteCollector $r) {
        $r->addRoute('GET', '/do-something', 'handler');
        $r->addRoute('GET', '/do-another-thing', 'handler');
        $r->addRoute('GET', '/do-something-else', 'handler');
    });
 */    // {id} must be a number (\d+)
    // $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
    // The /{title} suffix is optional
    // $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
});
