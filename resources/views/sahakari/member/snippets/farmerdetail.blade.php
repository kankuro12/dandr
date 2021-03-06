<div id="farmer-data" class="">
    {{-- <h4 class="title "> 
        <span>Farmer Detail</span>
    </h4>
    <hr > --}}
    {{-- d-flex justify-content-between card shadow p-2 --}}
    {{-- <span>

        <span class="btn btn-primary btn-sm toogle ml-2" data-on="true" data-collapse="#farmer-detail" >
            <span class="on">Hide</span>
            <span class="off">show</span>
        </span>
    </span> --}}
    <div class="row" id="farmer-detail">
        <div class="col-md-4">
            <label for="center_id">Farmer Collection Center (f2)</label>
            <select name="center_id" id="center_id" class="form-control show-tick ms ">
                @foreach(\App\Models\Center::all() as $c)
                <option value="{{$c->id}}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 ">
            <label for="center_id">Farmer No (f2)</label>
            <input type="text" placeholder="Farmer No" name="farmer_no" id="farmer_no" class="form-control">
        </div>
        <div class="col-md-2  ">
            <label for="">Has CC</label><br>
            <input type="checkbox" name="usecc" class="mr-2" value="1">Has Cooling Cost
        </div>
        <div class="col-md-2 ">
            <label for="">Has TS</label><br>
            <input type="checkbox" name="usetc" class="mr-2" value="1">Has TS <br>
        </div>
        <div class="col-md-2 ">
            <label for="f_rate">
                <input type="checkbox" name="userate" class="mr-2 switch" value="1" class="" data-switch="#f_rate">Fixed Rate
            </label>
            <input type="number" step="0.01" min="0" placeholder="Milk Rate" name="f_rate" id="f_rate" class="form-control">
        </div>
    </div>
    <hr >

</div>