<form id="editform" onsubmit="return editData(event);">
    <div class="card">


            <div class="row m-0">
                <div class="col-lg-3">
                    <label for="name">Item Name</label>
                    <input type="hidden" name="id" id="eid" value="{{ $item->id }}">
                    <div class="form-group">
                        <input value="{{ $item->title }}" type="text" id="ename" name="name" class="form-control next"
                            data-next="einum" placeholder="Enter item name" required>
                    </div>
                </div>

                <div class="col-lg-3">
                    <label for="name">Item Number</label>
                    <div class="form-group">
                        <input type="text" id="einum" name="number" value="{{ $item->number }}" name="text"
                            class="form-control next" data-next="ecprice" placeholder="Enter unique item number"
                            required>
                    </div>
                </div>
                @php
                    $w = env('use_wholesale', false);
                    $r = $w ? 4 : 6;
                @endphp
                <div class="col-lg-3">
                    <label for="cprice">Cost Price</label>
                    <div class="form-group">
                        <input type="number" id="ecprice" step="0.01" value="{{ $item->cost_price }}" name="cost_price"
                            min="0" class="form-control next" data-next="esprice" placeholder="Enter cost price"
                            required>
                    </div>
                </div>
                @if (!env('multi_stock',false))

                    @if ($w)
                        <div class="col-lg-3">
                            <label for="wprice">WholeSale Price</label>
                            <div class="form-group">
                                <input type="number" id="wprice" step="0.001" name="wholesale" min="0"
                                    class="form-control next" data-next="sprice" placeholder="Enter wholesale price"
                                    required value="{{ $item->wholesale }}">
                            </div>
                        </div>
                    @endif
                    <div class="col-lg-3">
                        <label for="sprice">Sell Price</label>
                        <div class="form-group">
                            <input type="number" id="esprice" step="0.01" value="{{ $item->sell_price }}" name="sell_price"
                                min="0" class="form-control next" data-next="estock" placeholder="Enter sell price"
                                required>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <label for="stock">Stock</label>
                        <div class="form-group">
                            <input type="number" id="estock" step="0.01" name="stock" value="{{ $item->stock }}" min="0"
                                class="form-control next" data-next="eunit" placeholder="Enter stock" required>
                        </div>
                    </div>
                @endif
                @if (!env('multi_package',false))

                <div class="col-lg-3">
                    <label for="stock">Unit Type</label>
                    <div class="form-group">
                        <input type="text" id="eunit" name="unit" value="{{ $item->unit }}" class="form-control"
                            placeholder="Enter unit type" required>
                    </div>
                </div>
                @else
                <div class="col-lg-4">
                    <label for="unit">Unit Type</label>
                    <div class="form-group">
                        <select name="conversion_id" id="conversion_id" class="form-control ms">
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}" {{$unit->id==$item->conversion_id?'selected':''}}>{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                <div class="col-lg-3">
                    <label for="unit">Reward (%)</label>
                    <div class="form-group">
                        <input type="number" id="ereward" name="reward" value="{{ $item->reward_percentage }}"
                            step="0.001" min="0" class="form-control" placeholder="Enter item reward percentage">
                    </div>
                </div>
                <div class="col-lg-3">
                    <label for="unit">Points</label>
                    <div class="form-group">
                        <input type="number" id="epoints" name="points" value="{{ $item->points }}" step="0.001"
                            min="0" class="form-control" placeholder="Enter item reward percentage">
                    </div>
                </div>
                {{-- <div class="col-12"></div> --}}

                @if (env('use_distributer', false))
                    <div class="col-lg-3">
                        <label for="dis_number">Distributer Number</label>
                        <div class="form-group">
                            <input type="text" id="edis_number" name="dis_number" value="{{ $item->dis_number }}"
                                class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <label for="dis_price">Distributer Rate</label>
                        <div class="form-group">
                            <input type="number" id="edis_price" name="dis_price" value="{{ $item->dis_price }}"
                                step="0.001" min="0" value="0" class="form-control">
                        </div>
                    </div>
                @endif
                <div class="col-12"></div>
                @if (env('use_online',false))
                <div class="col-lg-3">

                    <label for="image">Image</label>
                    <br>
                    <div style="height:100px;overflow:auto;">

                        <img src="{{ asset($item->image) }}" alt="">
                    </div>
                    <div class="form-group">
                        <input type="file" id="image" name="image" accept="image/*" class="form-control">
                    </div>
                </div>
                <div class="col-lg-9">
                    <label for="description">Description</label>
                    <div class="form-group">
                        <textarea id="description" rows="6" name="description" accept="image/*"
                            class="form-control">{{ $item->description }}</textarea>
                    </div>
                </div>
                @endif
            </div>
            <div class="py-3">
                <div class="row m-0">
                    <div class="col-md-3">
                        Collection Center
                    </div>
                    <div class="col-md-3">
                        Stock
                    </div>
                    <div class="col-md-3">
                        Rate
                    </div>
                    <div class="col-md-3">
                        Wholesale
                    </div>
                </div>
                @foreach ($centers as $center)
                    @php
                        $stock = $item->stock($center->id);
                    @endphp
                    <hr>
                    <div class="row m-0">
                        <div class="col-md-3">
                            <b>
                                {{ $center->name }}
                            </b>
                            <input type="hidden" name="center_ids[]" value="{{ $center->id }}">
                        </div>
                        <div class="col-md-3">
                            <input class="form-control" type="number" name="amount_{{ $center->id }}"
                                id="amount_{{ $center->id }}" min="0" step="0.01"
                                value="{{ $stock == null ? 0 : $stock->amount }}" required>
                        </div>
                        <div class="col-md-3">
                            <input class="form-control pos_edit_only_required" type="number" name="rate_{{ $center->id }}"
                                id="rate_{{ $center->id }}" min="0" step="0.01"
                                value="{{ $stock == null ? $item->sell_price : $stock->rate }}" {{$item->posonly==1?'required':''}}>
                        </div>
                        <div class="col-md-3">
                            <input class="form-control pos_edit_only_required" type="number" name="wholesale_{{ $center->id }}"
                                id="wholesale_{{ $center->id }}" min="0" step="0.01"
                                value="{{ $stock == null ? $item->wholesale : $stock->wholesale }}" {{$item->posonly==1?'required':''}}>
                        </div>
                    </div>
                @endforeach
            </div>
            <hr>
            <div class="row m-0">
                <div class="col-lg-3">
                    <input type="checkbox" name="trackstock" id="etrackstock" value="1"
                        {{ $item->trackstock == 1 ? 'checked' : '' }}>
                    <label for="trackstock">Track Stock</label>
                </div>
                <div class="col-lg-3">
                    <input type="checkbox" name="trackexpiry" id="etrackexpiry" value="1"
                        {{ $item->trackexpiry == 1 ? 'checked' : '' }}>
                    <label for="trackexpiry">Track Expiry</label>
                </div>
                <div class="col-lg-3">
                    <input type="checkbox" name="sellonline" id="esellonline" value="1"
                        {{ $item->sellonline == 1 ? 'checked' : '' }}>
                    <label for="sellonline">Sell Online</label>
                </div>
                <div class="col-lg-3">
                    <input type="checkbox" name="disonly" id="edisonly" value="1" {{ $item->disonly == 1 ? 'checked' : '' }}>
                    <label for="disonly">sell Distributer</label>
                </div>
                <div class="col-lg-3">
                    <input type="checkbox" onchange="posEditOnlyChange(this)" name="posonly" id="eposonly" value="1" {{ $item->posonly == 1 ? 'checked' : '' }}>
                    <label for="posonly">Sell POS</label>
                </div>
                <div class="col-lg-3">
                    <input type="checkbox" name="farmeronly" id="efarmeronly" value="1"
                        {{ $item->farmeronly == 1 ? 'checked' : '' }}>
                    <label for="farmeronly">Sell Farmer</label>
                </div>
                <div class="col-lg-3">
                    <input type="checkbox" name="taxable" id="etaxable" value="1" {{ $item->taxable == 1 ? 'checked' : '' }}>
                    <label for="taxable">Taxable</label>
                </div>
                <div class="col-lg-12"></div>
                <div class="col-lg-3">
                    <label for="tax">Tax/VAT</label>
                    <div class="form-group">
                        <input type="number" id="etax" name="tax" step="0.001" min="0" value="13" class="form-control"
                            value="{{ $item->tax }}">
                    </div>
                </div>
                <div class="col-lg-3">
                    <label for="expirydays">Expiary Days</label>
                    <div class="form-group">
                        <input type="number" id="eexpirydays" name="expirydays" step="0.001" min="0"
                            class="form-control" value="{{ $item->expirydays }}">
                    </div>
                </div>

                <div class="col-lg-3">
                    <label for="minqty">Min Online Qty</label>
                    <div class="form-group">
                        <input type="number" id="eminqty" name="minqty" step="0.001" min="0" class="form-control"
                            value="{{ $item->minqty }}">
                    </div>
                </div>
            </div>

    </div>
    <div class="modal-footer">
        <button class="btn btn-raised btn-primary waves-effect" type="submit">Update Data</button>
        <span class="btn btn-danger waves-effect" onclick="win.hide()">Close</span>
    </div>
</form>
