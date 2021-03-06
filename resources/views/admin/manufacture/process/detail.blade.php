@extends('admin.layouts.app')
@section('title')
    Manufacture Process - Detail
@endsection
@section('css')
@endsection
@section('head-title')
    <a href="{{ route('admin.manufacture.process.index') }}">Manufacture Process</a> / {{ $process->title }} / Batch
    #{{ $process->item_id }}.{{ $process->id }}
@endsection
@section('toobar')
@endsection
@section('content')
    @php
    $btnStage = ['', 'warning', 'primary', 'success', 'danger', 'danger', 'danger'];
    $textStage = ['', 'Pending', 'Processing', 'Finished', 'Canceled ', 'Canceled ', 'Canceled'];
    @endphp
    <span class="px-3 py-2 d-inline-block bg-{{ $btnStage[$process->stage] }} text-white">
        Status: {{ $textStage[$process->stage] }}
    </span>
    <hr>
    <div class="row">

        <div class="col-md-2">
            <strong>Item</strong> <br>
            {{ $process->title }}
        </div>
        <div class="col-md-2">
            <strong>Expected Yield</strong> <br>
            {{ $process->expected }} {{ $process->unit }}
        </div>
        <div class="col-md-2">
            <strong>Actual Yield</strong> <br>
            {{ $process->stage == 3 ? $process->actual . ' ' . $process->unit : '--' }}
        </div>
        <div class="col-md-2">
            <strong>Start Time</strong> <br>
            {{ $process->start }}
        </div>
        @if ($process->stage < 3)
            <div class="col-md-2">
                <strong>Expected End Time</strong> <br>
                {{ $process->expected_end }}
                <hr>
                <strong id="remaining_expected_end ">

                </strong>
            </div>
        @else
            <div class="col-md-2">
                <strong>End Time</strong> <br>
                {{ $process->end }}
            </div>
        @endif
        @if ($multiStock)
            <div class="col-md-2">
                <strong>Center/Branch</strong> <br>
                {{ $process->center }}
            </div>
        @endif
    </div>
    <hr>
    <h4 class="m-0">Raw Material {{$process->stage==1?"Estimated":"Used"}}</h4>
    <table class="table">
        <thead>
            <tr>
                <th>Item Name</th>
                <th>qty</th>
                @if ($multiStock)
                    <th>Center</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr>
                    <td>
                        {{ $item->title }}
                        ({{ $item->unit }})
                        <div id="stock_check_{{ $item->id }}" class="stock_check text-danger">

                        </div>
                    </td>
                    <td>
                        {{ $item->amount }}
                    </td>
                    @if ($multiStock)
                        <td>{{ $item->center }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
    @if ($process->stage > 2 && count($wastages) > 0)
        <h4 class="m-0">Wastage Raw Material </h4>
        <table class="table">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>qty</th>
                    {{-- @if ($multiStock)
                        <th>Center</th>
                    @endif --}}
                </tr>
            </thead>
            <tbody>
                @foreach ($wastages as $item)
                    <tr>
                        <td>
                            {{ $item->title }}
                            <div id="stock_check_{{ $item->id }}" class="stock_check text-danger">

                            </div>
                        </td>
                        <td>
                            {{ $item->amount }} {{ $item->unit }}
                        </td>
                        {{-- @if ($multiStock)
                            <td>{{ $item->center }}</td>
                        @endif --}}
                    </tr>
                @endforeach
            </tbody>
        </table>
        <hr>

    @endif
    @if ($process->stage > 2 && count($unused) > 0)

        <h4 class="m-0">Unused/Returned Raw Material </h4>
        <table class="table">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>qty</th>
                    {{-- @if ($multiStock)
                        <th>Center</th>
                    @endif --}}
                </tr>
            </thead>
            <tbody>
                @foreach ($unused as $item)
                    <tr>
                        <td>
                            {{ $item->title }}
                            <div id="stock_check_{{ $item->id }}" class="stock_check text-danger">

                            </div>
                        </td>
                        <td>
                            {{ $item->amount }} {{ $item->unit }}
                        </td>
                        {{-- @if ($multiStock)
                            <td>{{ $item->center }}</td>
                        @endif --}}
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($process->stage==1)
        <a href="{{route('admin.manufacture.process.edit',['id'=>$process->id])}}" class="btn btn-primary">Edit Process</a>

    @endif
    @if ($process->stage < 3)
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-next-{{$process->stage}}">
        {{$process->stage==1?"Start Manufacturing Process":"Finish Manufacturing Process"}}
      </button>
        <div class="modal fade" id="modal-next-{{$process->stage}}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        @if ($process->stage == 1)
                            <form action="{{ route('admin.manufacture.process.start.process', ['id' => $process->id]) }}"
                                method="post" id="start-process">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4">

                                        <div class="form-group">
                                            <label for="start">Start Datetime </label>
                                            <input type="datetime-local" onchange="changeStart(this)" name="start" id="start"
                                                class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="expected_end">Expected finish Datetime</label>
                                            <input type="datetime-local" name="expected_end" id="expected_end" class="form-control"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 pt-4">
                                        <button class="btn btn-primary "> Start Process</button>
                                    </div>
                                </div>

                            </form>
                        @elseif ($process->stage == 2)
                            <form action="{{ route('admin.manufacture.process.finish.process', ['id' => $process->id]) }}"
                                method="post" id="start-process">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4">

                                        <div class="form-group">
                                            <label for="start">Actual Yield </label>
                                            <input type="number" value="{{ $process->expected }}" step="0.0001"
                                                onchange="changeStart(this)" name="actual" id="actual" class="form-control" required>
                                        </div>

                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="end">Finish Time</label>
                                            <input type="datetime-local" name="end" id="end" class="form-control" required>
                                        </div>
                                    </div>

                                    <div class="col-md-4 pt-4">
                                        <button class="btn btn-primary " onclick="return prompt('Enter yes to finish process')=='yes';">
                                            Finish Process</button>
                                    </div>


                                </div>
                                <table class="table">
                                    <tr>
                                        <th>
                                            Item Name
                                        </th>
                                        <th>
                                            Wastage Amount
                                        </th>
                                        <th>
                                            Unused Amount
                                        </th>

                                    </tr>

                                    @foreach ($items as $item)
                                        <tr>
                                            <td>
                                                {{ $item->title }} {{ $item->unit }}
                                            </td>
                                            <td>
                                                <input type="number" step="0.001" value="0" name="amount_{{ $item->id }}">
                                            </td>
                                            <td>
                                                <input type="number" step="0.001" value="0" name="unused_amount_{{ $item->id }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>

                            </form>
                        @endif
                    </div>

                </div>
            </div>
        </div>
        {{-- <div class="shadow p-3 mb-3">
        </div> --}}
    @endif
    @if ($process->stage < 4)

        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modal-cancel-{{$process->stage}}">
            Cancel Manufacturing Process
        </button>
        <div class="modal fade" id="modal-cancel-{{$process->stage}}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-body">

                        <form action="{{ route('admin.manufacture.process.cancel.process', ['id' => $process->id]) }}" method="post" onsubmit="return cancelProcess(event,this);">
                            @csrf
                            <div class="form-group">
                                <label for="return_type">Process Cancel Type</label>
                                <select name="return_type" id="return_type" class="form-control ms" onchange="returnTypeChange(this)">
                                    <option value="5">No Raw Material Used</option>
                                    @if ($process->stage > 1)
                                        <option value="6">Partial Wastage</option>
                                        <option value="4">Full Wastage</option>
                                    @endif
                                </select>
                            </div>
                            @if ($process->stage > 1)
                                <div id="partial_cancel_items" style="display: none;">

                                    <table class="table">
                                        <tr>
                                            <th>
                                                Item Name
                                            </th>
                                            <th>
                                                Wastage Amount
                                            </th>
                                        </tr>

                                        @foreach ($items as $item)
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="manufacture_process_item_id[]"
                                                        value="{{ $item->id }}">
                                                    {{ $item->title }}
                                                </td>
                                                <td>
                                                    <input type="number" value="{{ $item->amount }}"
                                                        name="amount_{{ $item->id }}"> {{ $item->unit }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            @endif

                            <div class="py-2">
                                <button class="btn btn-danger" >Cancel Manufacture Process</button>
                            </div>
                        </form>
                    </div></div></div></div>
        {{-- <div class="shadow p-3">
        </div> --}}
    @endif


@endsection
@section('js')
    <script src="{{ asset('backend/plugins/select2/select2.min.js') }}"></script>
    <script>
        const currentDate = getDateTimeLocal(new Date('{{ $process->start }}'));
        const end = getDateTimeLocal(new Date('{{ $process->expected_end }}'));
    </script>
    @if ($process->stage == 1)
        <script>
            $(document).ready(function() {
                $('#start').val(currentDate);
                $('#expected_end').val(end);
                $('#start-process').submit(function(e) {
                    e.preventDefault();
                    checkStock(this)
                })
            });

            function startProcess(ele) {
                axios.post('{{ route('admin.manufacture.process.start.process', ['id' => $process->id]) }}', new FormData(
                        ele))
                    .then((res) => {
                        hideProgress();
                        window.location.reload();
                    })
                    .catch((err) => {
                        showNotification('bg-danger', "Process cannot be started");
                        hideProgress();
                    });

            }

            function checkStock(ele) {
                showProgress('Starting Manufacturing Process for {{ $process->title }}');
                axios.get('{{ route('admin.manufacture.process.check.stock.saved', ['id' => $process->id]) }}')
                    .then((res) => {
                        data = res.data;
                        if (data.hasstock) {
                            startProcess(ele);
                        } else {
                            data.msgs.forEach(msg => {
                                $('#stock_check_' + msg.id).html('Not Enough Stock');
                            });
                            hideProgress();
                        }

                    })
                    .catch((err) => {
                        showNotification('bg-danger', "Process cannot be started");

                        hideProgress();

                    });
            }

            function changeStart(ele) {
                const currentDate = new Date($('#start').val());
                const expectedDate = new Date(currentDate.valueOf() + {{ $process->finish_ms }});
                $('#expected_end').val(getDateTimeLocal(expectedDate));
            }
        </script>
    @elseif($process->stage == 2)
        <script>
            const countDownDate = new Date('{{ $process->expected_end }}').getTime();


            $(document).ready(function() {
                const x = setInterval(function() {

                    // Get today's date and time
                    const now = new Date().getTime();

                    // Find the distance between now and the count down date
                    const distance = countDownDate - now;

                    // Time calculations for days, hours, minutes and seconds
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    // Display the result in the element with id="demo"
                    document.getElementById("remaining_expected_end").innerHTML = days + "d " + hours + "h " +
                        minutes + "m " + seconds + "s ";

                    // If the count down is finished, write some text
                    if (distance < 0) {
                        clearInterval(x);
                        document.getElementById("remaining_expected_end").innerHTML = "Process Ended";
                    }
                }, 1000);
                $('#end').val(end);
            });
        </script>
    @endif

    @if ($process->stage < 4)
        <script>
            function returnTypeChange(ele) {
                if (ele.value == 6) {
                    $('#partial_cancel_items').show();
                } else {
                    $('#partial_cancel_items').hide();

                }
            }
        </script>
    @endif

    <script>
        function cancelProcess(e,ele){
            e.preventDefault();

            if(prompt('Enter yes to cancel process')=='yes'){
                showProgress("Canceling the process");
                axios.post(ele.action,new FormData(ele))
                .then((res)=>{
                    if(res.data.status){
                        window.location.reload();
                        hideProgress();
                    }else{
                        showNotification('bg-danger','Process Cannot be canceled');
                        hideProgress();

                    }
                })
            }
        }
    </script>
@endsection
