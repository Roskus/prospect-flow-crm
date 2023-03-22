@extends('layouts.app')

@section('content')
<header>
   <h1>{{ __('Order') }}</h1>
</header>

<div class="card">
    <div class="card-body">
    <form method="post" id="order-form" action="/order/save">
    @csrf
    <div class="row">
        <div class="col">
            <label for="customer_id">{{ __('Customer') }}</label>
            <input name="customer_id" id="customer_id" list="customer-list" required="required" class="form-control form-control-lg">

            <datalist id="customer-list">
                <option value="">{{ __('Choose') }}</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}"
                            @if($order->getCustomerId() == $customer->id) selected="selected" @endif>
                        {{ $customer->name }} ({{ $customer->business_name }})
                    </option>
                @endforeach
            </datalist>
        </div>

    </div>
    <div class="row">
        <div class="col">
            <label for="product_id">{{ __('Product') }}</label>
            <select name="product_id" id="product_id" onchange="Order.updatePrice(this)" class="form-control form-control-lg">
                <option value="" data-price="0">{{ __('Choose') }}</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" data-price="{{ $product->price}}">{{ $product->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col">
            <label for="quantity">{{ __('Quantity')}}</label>
            <input type="number" name="quantity" id="quantity" required="required" placeholder="{{ __('Quantity') }}" min="1" step="1" class="form-control form-control-lg">
        </div>
        <div class="col">
            <label for="price">{{ __('Price')}}</label>
            <input type="number" name="price" id="price" required="required" placeholder="{{ __('Price') }}" min="0" step="0.01" class="form-control form-control-lg">
        </div>
        <div class="col">
            <button type="button" name="btn-add-product" id="btn-add-product" onclick="Order.addItem()" class="btn btn-primary btn-lg btn-primary-outlined mt-4">
                <i class="las la-plus"></i>
            </button>
        </div>
    </div>
    <div class="row">
        <div class="col mt-2">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th width="60%">{{ __('Product') }}</th>
                        <th>{{ __('Price') }}</th>
                        <th>{{ __('Quantity') }}</th>
                        <th>{{ __('Subtotal') }}</th>
                    </tr>
                </thead>
                <tbody id="order-items">
                @if($order->items()->count() == 0)
                <tr id="row-no-data">
                    <td colspan="4">{{ __('No items') }}</td>
                </tr>
                @else
                    @foreach($order->items as $item)
                    <tr>
                        <td>{{ (!empty($item->product)) ? $item->product->name : '' }}</td>
                        <td>{{ $item->unit_price }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ ($item->unit_price * $item->quantity) }}</td>
                    </tr>
                    @endforeach
                @endif
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2">&nbsp;</th>
                        <th class="text-right">{{ __('Total') }}</th>
                        <th>
                            <input type="number" name="total" id="total" value="{{ $order->getTotal() }}" readonly="readonly" step="0.01" class="form-control form-control-lg" style="max-width: 200px">
                        </th>
                    </tr>
                </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div>
        <a href="{{ url('/order')}}" class="btn btn-lg btn-outline-secondary">{{ __('Cancel') }}</a>
        <button type="submit" class="btn btn-lg btn-primary">{{ __('Save') }}</button>
    </div>
    <input type="hidden" name="id" value="{{ ($order->getId()) ?? $order->getId() }}">
    </form>
    </div>
</div><!--./card-->

<template id="product-row">
<tr>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
</tr>
</template>
@push('scripts')
<script src="{{ asset('/asset/js/Order.js') }}"></script>
@endpush
@endsection
