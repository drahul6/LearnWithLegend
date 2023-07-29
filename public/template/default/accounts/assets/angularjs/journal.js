function InvolvedAccounts($lines = []) {
    this.$lines = $lines;
    this.$total_credit = 0;
    this.$total_debit = 0;
    this.$subtotal_credit = 0;
    this.$subtotal_debit = 0;
    this.chart_of_accounts_available = [];



    this.choose = function($line, $value) {
        // console.log('choosing');
    }


    this.populate_options = function($query) {
        let $this = this;
        if ($query.length < 2) { return; }
        $.ajax({
            type: "POST",
            url: `${$base_url}/accounts/retrieve_account?search=${$query}`,
            data: null,
            success: function(data) {
                $scope = angular.element($('#journal_table')).scope();
                $scope.$charts_of_accounts = data.data;


                $('#chart_of_accounts_available').html(data.line);
            },
            error: function(data) {},
            complete: function() {}
        });




    }

    this.set_credit_and_debit_limit = function() {
        // alert('setting');
    }



    this.sumCreditAndDebit = function() {
        this.getTotalCredit();
        this.getSubTotalCredit();
        this.getTotalDebit();
        this.getSubTotalDebit();
    }


    this.getTotalCredit = function() {
        $credit = 0;
        for (x in this.$lines) {
            $line = $lines[x];
            $credit = $credit + (parseInt($line.credit) || 0);
        }

        this.$total_credit = ($credit);
    }

    this.getSubTotalCredit = function() {
        $credit = 0;
        for (x in this.$lines) {
            $line = $lines[x];
            $credit = $credit + (parseInt($line.credit) || 0);
        }

        this.$subtotal_credit = ($credit);
    }


    this.getTotalDebit = function() {
        $debit = 0;
        for (x in this.$lines) {
            $line = $lines[x];
            $debit = $debit + (parseInt($line.debit) || 0);
        }

        this.$total_debit = ($debit);
    }



    this.getSubTotalDebit = function() {
        $debit = 0;
        for (x in this.$lines) {
            $line = $lines[x];
            $debit = $debit + (parseInt($line.debit) || 0);
        }

        this.$subtotal_debit = ($debit);
    }


    this.add_line = function() {
        this.$lines.push({
            'journal_id': $journal_id,
            'chart_of_account_id': null,
            'chart_of_account_number': null,
            'description': null,
            'credit': null,
            'debit': null
        });

    }


    if (this.$lines.length == 0) {
        this.add_line();
    }


    this.remove_line = function($line) {

        for (x in this.$lines) {
            $line_obj = this.$lines[x];
            if ($line_obj == $line) {
                this.$lines.splice(x, 1);
            }
        }

        this.sumCreditAndDebit();
    }
    this.sumCreditAndDebit();

}


function Journal($data) {

    this.$data = $data;
    this.$involved_accounts = new InvolvedAccounts($data.involved_accounts);
    this.$data.journal_date = (this.$data.journal_date != null) ? new Date(this.$data.journal_date) : new Date();
    this.$attached_files = [];


    this.add_files = function($file_input) {
        this.$attached_files = $file_input.files;
        angular.element($file_input).scope().$apply();
    }


    this.attempt_save = function($mode, $message = null) {
        // console.log(this.$data);

        window.$confirm_dialog = new DialogJS(this.save, [$mode], $message, this);
    }



    this.save = function($publish) {
        // console.log(this)
        this.$data.published_status = $publish;
        $form = new FormData();

        $form.append('journal', JSON.stringify(this.$data));
        $form.append('involved_accounts', JSON.stringify(this.$involved_accounts));

        for (var i = 0; i < this.$attached_files.length; i++) {

            $form.append('attachments[]', this.$attached_files[i]);
        }




        $.ajax({
            type: "POST",
            url: $base_url + "/journals/update_journal/" + $journal_id,
            cache: false,
            contentType: false,
            processData: false,
            data: $form,
            success: function(data) {
                window.notify();
                if (typeof(data) == 'object') {
                    // window.location.href = data.journal_link;
                }

            },
            error: function(data) {
                //alert("fail"+data);
            }
        });
    }
}







app.controller('JournalController', function($scope, $http) {

    $scope.$name = "isareal";


    $scope.fetch_page_content = function() {

        $http.get($base_url + '/journals/find/' + $journal_id)
            .then(function(response) {

                // let $involved_accounts = (response.data.involved_accounts);
                $scope.$journal = new Journal(response.data.journal);
                $scope.$charts_of_account_options = (response.data.charts_of_account_options);

            });

    }


    $scope.fetch_page_content();


});