$("#barcode").keydown(function (e) {
    if (e.which == 13) {
        barcode = this.value;
        if (barcode.length > 0) {
            billpanel.addItem(barcode);
        }
    }
});

$("#barcode").focus(function (e) {
    e.preventDefault();
    console.log(e, "focused");
    barcode = this.value;
    if (barcode.length > 0) {
        this.select();
    }
});

function addItem(barcode) {
    console.log(barcode);
}

const producturl = "";

var billpanel = {
    products: [],
    index: 0,
    billitems: [],
    total: {
        total: 0,
        discount: 0,
        taxable: 0,
        tax: 0,
        grandtotal: 0,
        paid: 0,
        due: 0,
    },
    resetInput: function () {
        for (const key in this.total) {
            this.total[key] = 0;
            this.setVal(key, 0);
        }
    },
    setVal: function (name, value) {
        $("#input-" + name).val(value);
    },
    getVal: function (name) {
        return $("#input-" + name).val();
    },
    calculateTotal: function () {
        tot = 0;
        this.billitems.forEach((bi) => {
            tot += bi.item.rate * bi.amount;
        });

        this.setVal("total", tot);
        this.total.total = tot;
        this.total.discount = parseFloat(this.getVal("discount"));
        if (isNaN(this.total.discount)) {
            this.total.discount = 0;
        }
        this.total.taxable = this.total.total - this.total.discount;
        this.setVal("taxable", this.total.taxable);
        this.total.tax = parseFloat(this.getVal("tax"));
        if (isNaN(this.total.tax)) {
            this.total.tax = 0;
        }
        this.total.grandtotal = this.total.taxable + this.total.tax;
        this.setVal("grandtotal", this.total.grandtotal);
        this.total.paid = parseFloat(this.getVal("paid"));
        if (isNaN(this.total.paid)) {
            this.total.paid = 0;
        }
        this.total.due = this.total.grandtotal - this.total.paid;
        if (this.total.due < 0) {
            this.total.due = 0;
        }
        this.setVal("due", this.total.due);
    },
    ele: function () {
        return $("#bill-items");
    },
    init: function () {
        var p = Array();
        showProgress('Loading Items');
        axios
            .get(itemsURL)
            .then((res) => {
                items = res.data;
                for (let index = 0; index < items.length; index++) {
                    const item = items[index];
                    barcode = format(index.toString(), 5);
                    p[item.barcode] = {
                        id: item.id,
                        name: item.name,
                        rate: item.rate,
                    };
                }
                this.products = p;

                html = "<option></option>";

                Object.keys(p).forEach((key) => {
                    element = p[key];
                    html +=
                        "<option value='" +
                        key +
                        "' data-rate='" +
                        element.rate +
                        "'>" +
                        element.name +
                        "</option>";
                });

                $("#item-name").html(html);
                $("#item-name").select2();
                hideProgress();
            })
            .catch((err) => {
              hideProgress();

            });

        setInterval(() => {
            pushData();
        }, 10000);
        pushData();
    },
    setRate: function () {
        var rate = $("#item-name option:selected").data("rate");
        $("#item-rate").val(rate);
        $("#item-qty").focus();
    },
    plus: function (key) {
        console.log(key);
        billItem = this.billitems[key];
        if (billItem != undefined) {
            this.billitems[key].amount += 1;
        }
        this.updateBillItem(this.billitems[key]);
    },
    minus: function (key) {
        console.log(key);
        billItem = this.billitems[key];
        if (billItem != undefined) {
            this.billitems[key].amount -= 1;
        }
        if (this.billitems[key].amount > 0) {
            this.updateBillItem(this.billitems[key]);
        } else {
            this.removeBillItem(key);
        }
    },
    removeBillItem: function (key) {
        $("#bill-item-" + key).remove();
        delete(this.billitems[key]);
        // console.log($temparr);
        // this.billitems = $temparr;
        this.calculateTotal();
    },
    updateBillItem: function (billItem) {
        key = billItem.item.id.toString();
        $("#bill-item-amount-" + key).html(billItem.amount);
        $("#bill-item-total-" + key).html(billItem.amount * billItem.item.rate);
        this.calculateTotal();
    },
    renderBillItem: function (item) {
        key = item.id.toString();
        billItem = this.billitems[key];
        html = "<tr class='bill-item' id='bill-item-" + key + "'>";
        html += "<td>" + item.name + "</td>";
        html += "<td>" + item.rate + "</td>";
        html += "<td> <div  class='qty'>";
        html +=
            "<span class='btn-qty' onclick='billpanel.plus(\"" +
            key +
            "\")'><img src='images/plus.svg' class='w-100'></span>";
        html +=
            "<span class='qty-value' id='bill-item-amount-" +
            key +
            "'>" +
            billItem.amount +
            "</span>";
        html +=
            "<span class='btn-qty' onclick='billpanel.minus(\"" +
            key +
            "\")'><img src='images/sub.svg' class='w-100'></span>";
        html += "</div></td>";

        html +=
            "<td id='bill-item-total-" +
            key +
            "'>" +
            item.rate * billItem.amount +
            "</td>";
        html += "</tr>";
        this.ele().append(html);
        this.calculateTotal();
    },
    addItem: function (barcode) {
        item = this.products[barcode];
        if (item == undefined) {
            $.notify("Not Item found With Barcode " + barcode, {
                className: "error",
            });
            $("#barcode").focus();
            $("#barcode").select();
        } else {
            key = item.id.toString();
            billItem = this.billitems[key];
            if (billItem == undefined) {
                this.billitems[key] = {
                    item: item,
                    amount: 1,
                };
                this.renderBillItem(item);
            } else {
                this.billitems[key].amount += 1;
                this.updateBillItem(this.billitems[key]);
            }
            console.log(this.billitems);
        }
        console.log(item);
        $("#barcode").val("");
        $("#barcode").focus();
    },
    addItemSelect: function () {
        barcode = $("#item-name").val();
        console.log(barcode);
        qty = parseFloat($("#item-qty").val());
        if (isNaN(qty)) {
            $.notify("Please Enter Qty", {
                className: "error",
            });
            return;
        } else {
            if (qty <= 0) {
                $.notify("Please Enter Qty", {
                    className: "error",
                });
                return;
            }
        }
        item = this.products[barcode];
        if (item == undefined) {
            $.notify("Please Select A Item", {
                className: "error",
            });
            $("#item-name").focus();
            $("#item-name").select();
        } else {
            key = item.id.toString();
            billItem = this.billitems[key];
            if (billItem == undefined) {
                this.billitems[key] = {
                    item: item,
                    amount: qty,
                };
                this.renderBillItem(item);
            } else {
                this.billitems[key].amount += qty;
                this.updateBillItem(this.billitems[key]);
            }
            $("#item-name").val(null).trigger("change");
            $("#item-rate").val("");
            $("#item-qty").val("");
            console.log(this.billitems);
        }
    },
    cancelBill: function () {
        if (confirm("Do You Want To Cancel Current Bill")) {
            this.resetInput();
            this.billitems = [];
            this.ele().html("");
        }
    },
    customerSearch:function(){
        searchType=$('input[name="radio_customer_search"]:checked').val();
        keyword=$('#input_customer_search').val();
        data={};
        if(searchType==1){
            if(keyword.length<8){
                return;
            }
            data={"phone":keyword};
        }else{
            if(keyword.length<2){
                return;
            }
            data={"name":keyword};

        }
        axios.post(customerSearchURL,data)
        .then((res)=>{
            console.log(res);
        })
        .catch((err)=>{});
    }
};

$(function () {
    billpanel.init();
});