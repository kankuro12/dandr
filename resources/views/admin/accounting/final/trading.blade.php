<h4 class="text-center mb-1">
    Trading Account <br> from {{ _nepalidate($range[1]) }} to {{ _nepalidate($range[2]) }}
</h4>
<div class="d-flex justify-content-between">
    <strong>Dr</strong>
    <strong>Cr</strong>
</div>


<div class="d-flex">
    <div style="flex: 1">
        <table class="table table-bordered mb-0">
            <thead>
                <tr>
                    <th class="w-40">
                        Particular
                    </th>
                    <th class="w-10">Amount</th>
                </tr>
            </thead>
            <tbody id="tradingAccDataDR">
                <tr class="main">
                    <td>
                        To Opening Stock
                    </td>
                    <td>
                        {{ $opening }}
                    </td>
                </tr>
                @if ($trading->purchase > 0)

                    @if ($showDetail)
                        <tr class="main">
                            <td>
                                To Purchase
                            </td>
                            <td></td>
                        </tr>
                        <tr class="sub">
                            <td>
                                Milk Purchase
                            </td>
                            <td>
                                {{ $trading->milk ?? 0 }}

                            </td>
                        </tr>
                        <tr class="sub">
                            <td>
                                Goods Purchase
                            </td>
                            <td>
                                {{ $trading->supplier ?? 0 }}
                            </td>
                        </tr>
                        <tr class="main">
                            <td>
                                Total Purchase
                            </td>
                            <td>
                                {{ $trading->purchase }}
                            </td>
                        </tr>
                    @else
                        <tr class="main">
                            <td>
                                To Purchase
                            </td>
                            <td>
                                {{ $trading->purchase }}
                            </td>
                        </tr>
                    @endif
                @endif
                @if ($trading->purchaseExpense > 0)
                    <tr class="main">
                        <td>
                            To Purchase Expenses
                        </td>
                        <td>
                            {{ $trading->purchaseExpense }}
                        </td>
                    </tr>
                @endif
                @if ($trading->status == 'profit')
                    <tr class="main">
                        <td>
                            To Gross Profit
                        </td>
                        <td>
                            {{ $trading->profit }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    <div style="flex: 1">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="w-40">
                        Particular
                    </th>
                    <th class="w-10">Amount</th>
                </tr>
            </thead>
            <tbody id="tradingAccDataCR">
                @php

                @endphp
                @if ($trading->sales > 0)
                    @if ($showDetail)
                        <tr class="main">
                            <td>
                                By Sales
                            </td>
                            <td></td>
                        </tr>
                        <tr class="sub">
                            <td>
                                Counter Sales
                            </td>
                            <td>
                                {{ $trading->counter }}
                            </td>
                        </tr>
                        <tr class="sub">
                            <td>
                                Sales to Farmer
                            </td>
                            <td>
                                {{ $trading->farmer ?? 0 }}
                            </td>
                        </tr>
                        <tr class="sub">
                            <td>
                                Sales to Distributer
                            </td>
                            <td>
                                {{ $trading->distributer ?? 0 }}
                            </td>
                        </tr>
                        <tr class="main">
                            <td>
                                Total Sales
                            </td>
                            <td>
                                {{ $trading->sales }}
                            </td>
                        </tr>
                    @else
                        <tr class="main">
                            <td>
                                By Sales
                            </td>
                            <td>
                                {{ $trading->sales }}
                            </td>
                        </tr>
                    @endif
                @endif
                @if ($closing>0)
                    <tr class="main">
                        <td>
                            By Closing Stock
                        </td>
                        <td>
                            {{ $closing }}
                        </td>
                    </tr>

                @endif
                @if ($trading->status == 'loss')
                    <tr class="main">
                        <td>By Gross Loss</td>
                        <td>
                            {{ $trading->loss }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
<div class="d-flex ">
    <div style="flex:1">
        <table class="table table-bordered">
            <tr class="main">
                <td class="w-40">Total</td>
                <td class="w-10">{{ $trading->total }}</td>
            </tr>
        </table>
    </div>
    <div style="flex:1">
        <table class="table table-bordered">
            <tr class="main">
                <td class="w-40">Total</td>
                <td class="w-10">{{ $trading->total }}</td>
            </tr>
        </table>
    </div>
</div>
