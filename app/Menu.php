<?php

namespace App;

class Menu
{
    public static function get()
    {
        return [
            "Farmer" => [
                'code' => '01',
                'link' => null,
                'icon' => 'apps',
                'text' => "Farmer",
                'children' => [
                    ["Farmer List", '01.01', route('admin.farmer.list')],
                    ["Advance", '01.10', route('admin.farmer.advance')],
                    ["Farmer Payment", '01.05', route('admin.farmer.due')],
                    ["Account Opening", '01.06', route('admin.farmer.due.add.list')],
                    ["Milk Payment", '01.07', route('admin.farmer.milk.payment.index')],
                    ["Farmer Sell", '01.08', route('admin.sell.item.index')],
                ],
            ],
            "milk" => [
                'code' => '02',
                'link' => null,
                'icon' => 'apps',
                'text' => "Milk Collection",
                'children' => [
                    ["Milk Collection", '02.01', route('admin.milk.index')],
                    ["SNF FAT", '02.04', route('admin.snf-fat.index')],
                ],
            ],
            "item" => [
                'code' => '03',
                'link' => null,
                'icon' => 'apps',
                'text' => "Items",
                'children' => [
                    ["Items", '03.04', route('admin.item.index')],
                    ["Stock Out", '03.05', route('admin.item.stockout-list')],
                ],
            ],
            "distributer" => [
                'code' => '04',
                'link' => null,
                'icon' => 'apps',
                'text' => "Distributers",
                'children' => [
                    ["Distributer List", '04.01', route('admin.distributer.index')],
                    ["Distributer Sell", '04.09', route('admin.distributer.sell')],
                    ["Distributer Payment", '04.05', route('admin.distributer.payemnt')],
                    ["Account Opening", '04.06', route('admin.distributer.detail.opening')],
                    ["Credit List", '04.10', route('admin.distributer.credit.list')],
                    ["SNF FAT", '04.08', route('admin.distributer.snffat.index')],
                    ["Milk Collection", '04.07', route('admin.distributer.MilkData.index')],
                ],
            ],
            "staff" => [
                'code' => '05',
                'link' => null,
                'icon' => 'apps',
                'text' => "Staff Manage",
                'children' => [
                    ["Employees", '05.01', route('admin.employee.index')],
                    ["Account Opening", '05.05', route('admin.employee.account.index')],
                    ["Advance", '05.06', route('admin.employee.advance')],
                    ["Salary Pay", '05.07', route('admin.salary.pay')],
                ],
            ],

            "expese" => [
                'code' => '06',
                'link' => null,
                'icon' => 'apps',
                'text' => "Manage Expense",
                'children' => [
                    ["Expense Categories", '06.01', route('admin.expense.category')],
                    ["Expenses", '06.05', route('admin.expense.index')],
                ],
            ],

            "supplier" => [
                'code' => '07',
                'link' => null,
                'icon' => 'apps',
                'text' => "Suppliers",
                'children' => [
                    ["Supplier List", '07.01', route('admin.supplier.index')],
                    ["Purchase Bill", '07.05', route('admin.supplier.bill')],
                    ["Supplier Payment", '07.09', route('admin.supplier.pay')],
                    ["Opening Balance", '07.10', route('admin.supplier.previous.balance')],
                ],
            ],

            "customer" => [
                'code' => '08',
                'link' => null,
                'icon' => 'apps',
                'text' => "Customers",
                'children' => [
                    ["List", '08.01', route('admin.customer.home')],
                    ["Payment", '08.02', route('admin.customer.payment.index')],
                ],
            ],

            "pos" => [
                'code' => '09',
                'link' => null,
                'icon' => 'apps',
                'text' => "POS",
                'children' => [
                    ["POS Interface", '09.01', url('/pos/day')],
                    ["Search Bills", '09.02', route('admin.pos.billing.index')],
                    ["Reprint Bills", '09.03', route('admin.pos.billing.print')],
                    ["Sales Returns", '09.04', route('admin.pos.billing.return')],
                ],
            ],


            "pos setting" => [
                'code' => '10',
                'link' => null,
                'icon' => 'apps',
                'text' => "POS Setting",
                'children' => [
                    ["Day Management", '10.01', route('admin.counter.day.index')],
                    ["Counters", '10.02', route('admin.counter.home')],
                    ["Offers", '10.03', route('admin.offers.index')],
                ],
            ],

            "Payment setting" => [
                'code' => '11',
                'link' => null,
                'icon' => 'apps',
                'text' => "Payment Setting",
                'children' => [
                    ["Banks", '11.01', route('admin.bank.index')],
                    ["Payment Gateways", '11.02', route('admin.gateway.index')],
                ],
            ],

            "Reports" => [
                'code' => '12',
                'link' => null,
                'icon' => 'apps',
                'text' => "Reports",
                'children' => [
                    ["Reports", '12.01', route('admin.report.home')],
                ],
            ],

        ];
    }
}