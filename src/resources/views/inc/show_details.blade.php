{{-- Show the details --}}
<div class="row">
@if(isset($currentStatistics))
    <div class="col-md-3">
        <div class="row">
        @foreach ($currentStatistics as $detail)
            <!-- load the view from the application if it exists, otherwise load the one in the package -->
            @php if(isset($detail['type'])) { $detailType = $detail['type']; } else { $detailType = 'text'; } @endphp
            @if(view()->exists('vendor.backpack.crud.details.'.$detailType))
                @include('vendor.backpack.crud.details.'.$detailType, array('detail' => $detail))
            @else
                @include('crud::details.'.$detailType, array('detail' => $detail))
            @endif

            <div class="clearfix"></div>

        @endforeach
        </div>
    </div>
@endif

<div class="col-md-9">
    <table class="table table-striped table-bordered">
        <tbody>
        @foreach ($details as $detail)
            <tr>
                <td>
                    <strong>{{ $detail['label'] }}</strong>
                </td>
                <td>
                <!-- load the view from the application if it exists, otherwise load the one in the package -->
                @php if(isset($detail['type'])) { $detailType = $detail['type']; } else { $detailType = 'text'; } @endphp
                @if(view()->exists('vendor.backpack.crud.details.'.$detailType))
                    @include('vendor.backpack.crud.details.'.$detailType, array('detail' => $detail))
                @else
                    @include('crud::details.'.$detailType, array('detail' => $detail))
                @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

</div>
