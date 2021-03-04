<section class="event">

    <div class="card" style="padding: 1rem">

        <div>
            <div class="flex" style="margin-bottom: 1rem;">
            <h4>Bleskové správy</h4>
            <i class="fas fa-rss"></i>
            </div>

            <div class="level">
            <div>domáce</div>
            <div>zahraničné</div>
            <div>tlačové</div>
            </div>

        </div>

        {{--<div class="card-body">--}}
            {{--@forelse( $rss->channel->item as $item)--}}
                {{--<div style="margin-bottom: .5rem">--}}
                    {{--<span title="{{ $item->title }}" style="font-weight: bold">{{ $item->title }}</span><br>--}}
                    {{--<span>{{ $item->description }}</span><br>--}}


                    {{--<span>{{ date('H:i, d.M.Y', $date) }}</span>--}}

                    {{--<span style="font-style: italic" title="Tlačová kancelária konferencie biskupov Slovenska">Informuje TKKBS</span>--}}
                {{--</div>--}}
            {{--@empty--}}
            {{--@endforelse--}}
        {{--</div>--}}
    </div>

</section>

