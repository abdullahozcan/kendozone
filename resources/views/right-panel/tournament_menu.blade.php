<?php
$generatedTreeCount = $tournament->trees->groupBy('championship_id')->count();
$settingSize = $tournament->championshipSettings->count();
$categorySize = $tournament->championships->count();
if (Route::currentRouteName() != 'tournaments.edit') {
    $baseUrl = route('tournaments.edit', ['tournament' => $tournament->slug]);

} else {
    $baseUrl = "";
}

?>
<!-- Detached sidebar -->
<div class="sidebar-detached">
    <div class="sidebar sidebar-default">
        <!-- Sub navigation -->
        <div class="category-title">
            <span>{{ $tournament->name }}</span>
            <ul class="icons-list">
                <li><a href="#" data-action="collapse"></a></li>
            </ul>
        </div>
        <div class="category-content no-padding">
            <ul class="navigation navigation-alt navigation-accordion">
                <li><a href="{{ $baseUrl }}#tab1"><i class="icon-trophy2"></i> {{ trans('core.general') }}
                        @if(!isNullOrEmptyString($tournament->registerDateLimit) && !isNullOrEmptyString($tournament->fightingAreas) && $tournament->level_id!=1)
                            <span class="badge badge-success"><i class=" icon icon-checkmark2"></i></span>
                        @endif
                    </a></li>
                <li><a href="{{ $baseUrl }}#tab2"><i class="icon-location4"></i> {{trans('core.venue')}}
                        @if ($tournament->venue != null)
                            <span class="badge badge-success" id="venue-status">
                                        <i class=" icon icon-check"></i>
                                    </span>
                        @endif


                    </a></li>
                <li><a href="{{ $baseUrl }}#tab3">
                        <i class="icon-cog2"></i>{{trans_choice('categories.category',2)}}
                        <?php
                        if ($settingSize > 0 && $settingSize == $categorySize)
                            $class = "badge-success";
                        else
                            $class = "badge-primary";
                        ?>
                        <div class="badge {!! $class !!}" id="categories-status">
                            <span class="category-size">{{ $settingSize  }}</span> / {{ $categorySize }}
                        </div>
                    </a></li>


                <li><a href="{{ URL::action('CompetitorController@index',$tournament->slug) }}"
                       id="competitors">
                        <i class="icon-users"></i>
                        {{trans_choice("core.competitor",2)}}
                        @if($tournament->competitors_count>8)
                            <span class="badge badge-success">{{$tournament->competitors_count }}</span>
                        @else
                            <span class="badge badge-primary">{{$tournament->competitors_count}}</span>
                        @endif

                    </a>
                </li>
                <li><a href="{{ URL::action('TreeController@index',$tournament->slug) }}">
                        <i class="icon-tree7"></i> {{trans("core.see_trees")}}
                        @if ($tournament->trees->groupBy('championship_id')->count() < $categorySize)
                            <span class="badge badge-primary">{{ $generatedTreeCount }}
                                / {{ $categorySize }}</span>
                        @else
                            <span class="badge badge-success">{{ $generatedTreeCount }}
                                / {{ $categorySize }}</span>
                        @endif


                    </a>
                </li>
                @if ($tournament->hasTeamCategory())
                    <li><a href="{{ URL::action('TeamController@index',$tournament->slug) }}"><i
                                    class="icon-collaboration"></i>{{ trans_choice('core.team',2) }}
                            @if($tournament->teams_count>2)
                                <span class="badge badge-success">{{$tournament->teams_count }}</span>
                            @else
                                <span class="badge badge-primary">{{$tournament->teams_count}}</span>
                            @endif
                        </a></li>
                @endif
            </ul>
        </div>
        <!-- /sub navigation -->
    </div>
    <br/>
    <div class="panel panel-nav">
        <div class="panel-heading">
            <h6 class="panel-title">{{ trans('core.share_tournament') }}<a class="heading-elements-toggle"><i
                            class="icon-more"></i></a></h6>
            <div class="heading-elements">
                <ul class="icons-list">
                    <li><a data-action="collapse"></a></li>
                </ul>
            </div>
        </div>

        <div class="panel-body">
            <div class="row">
                <div class="col-md-12 mb-20 text-center">
                    <a href="#" id="shareBtnShow" title="{{ trans('core.share_to_facebook') }}"
                       alt="{{ trans('core.share_to_facebook') }}">
                        <img src="{{ url('/images/brands/facebook.png') }}" class="img-circle img-xs"
                             alt="{{ trans('core.share_to_facebook') }}"/></a>
                    <a href="https://twitter.com/intent/tweet?url={{ URL::action('TournamentController@show',$tournament->slug) }}&text={{trans('core.check_the_tournament')}}{{ $tournament->name }}"
                       title="{{ trans('core.share_to_twitter') }}" alt="{{ trans('core.share_to_twitter') }}">
                        <img src="{{ url('/images/brands/twitter.png') }} "
                             class="img-circle img-xs twitter-share-button"
                             alt="{{ trans('core.share_to_twitter') }}"/></a>
                    <a href="https://plus.google.com/share?url={!! URL::action('TournamentController@show',$tournament->slug) !!}"
                       title="{{ trans('core.share_to_googleplus') }}"
                       alt="{{ trans('core.share_to_googleplus') }}"
                       onclick="javascript:window.open(this.href,
  '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;">
                        <img src="{{ url('/images/brands/googleplus.png') }}" class="img-circle img-xs"
                             alt="{{ trans('core.share_to_googleplus') }}"></a>
                </div>
            </div>
            <input value="{{ URL::action('TournamentController@show',$tournament->slug) }}" class="p-10 full-width">
        </div>
    </div>

    {{-- If open Tournament--}}


    @can('edit',$tournament)


        <div class="row">
            <div class="col-md-12">
                <p><a id="shareBtn" type="button"
                      class="btn btn-fb btn-labeled btn-xlg" style="width: 100%"><b>
                            <i class="icon-facebook"></i></b>{{ trans('core.invite_competitors_with_fb') }}
                    </a>
                </p>

            </div>

        </div>
        <div class="row">
            <div class="col-md-12">
                <p><a href="{!!   URL::action('InviteController@create',  $tournament->slug) !!}" type="button"
                      class="btn btn-primary btn-labeled btn-xlg" style="width: 100%"><b>
                            <i class="icon-envelope"></i></b>{{ trans('core.invite_competitors_with_email') }}
                    </a>
                </p>

            </div>

        </div>
    @endcan
    <br/>

</div>

<!-- /detached sidebar -->