@extends('admin.layouts.app')
@section('title', 'POS Bill')

@section('head-title', 'POS Bill')
@section('toobar')
    <a class="btn btn-primary" href="{{route('admin.pos.billing.print')}}">Reprint Bill</a>
    <a class="btn btn-primary" href="{{route('admin.pos.billing.return')}}">Return Bill</a>

@endsection
@section('content')
    
   

    <div class="pt-2 pb-2">
        @include('admin.layouts.daterange')
    </div>
    <div class="pt-2 pb-2">
        <span class="mx-2">
            <input type="checkbox" name="canceled" id="canceled" value="1"> Show Canceled
        </span>
    </div>
    <div class="row ">
        <div class="col-md-12">
            <button class="btn btn-primary" onclick="loadData()">Load Data</button>
        </div>
    </div>
    <hr>
    <div class="pt-2 pb-2">
        <input type="text" id="sid" placeholder="Search">
    </div>
    <div class="table-responsive">
        <table id="" class="table table-bordered table-striped table-hover js-basic-example dataTable">
            <thead>
                <tr>
                    <th>Bill No.</th>
                    <th>Date</th>
                    <th>Customer Name</th>
                    {{-- <th></th> --}}
                    <th>Total (Rs.)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="billData">

            </tbody>
        </table>
    </div>
@endsection
@section('js')
    <script src="{{ asset('backend/plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('backend/js/pages/forms/advanced-form-elements.js') }}"></script>
    <script>
        var customer_id=-1;
        var customer_id=-1;
        function loadData() {

            var data={
                'year':$('#year').val(),
                'month':$('#month').val(),
                'session':$('#session').val(),
                'week':$('#week').val(),
                'center_id':$('#center_id').val(),
                'date1':$('#date1').val(),
                'date2': $('#date2').val(),
                'type':$('#type').val(),
                'fy':$('#fiscalyear').val(),
                "customer_id":customer_id,
                'canceled':document.getElementById('canceled').checked?1:0
            };
            axios({
                    method: 'post',
                    url: '{{ route('admin.pos.billing.index') }}',
                    data:data
                })
                .then(function(response) {
                    // console.log(response.data);
                    $('#billData').html(response.data);
                    initTableSearch('sid', 'billData', ['name', 'billno']);
                })
                .catch(function(response) {
                    //handle error
                    console.log(response);
                });
        }

        function loadDetail(id,billno){
            win.showPost("Bill Detail - " + billno, '{{ route('admin.pos.billing.detail') }}', {
                "id": id
            })
        }

        window.onload = function() {
            $('#type').val(0).change();
            loadData();
        };

    </script>
@endsection
