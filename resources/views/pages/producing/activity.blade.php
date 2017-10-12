<?php
/**
 * This file (tasks.blade.php) was created on 06/24/2016 at 15:44.
 * (C) Max Cassee
 * This project was commissioned by HU University of Applied Sciences.
 */
?>
@extends('layout.HUdefault')
@section('title')
    Activiteiten
@stop
@section('content')
    <div class="container-fluid">
        <script>
            $(document).ready(function() {
                $('#custom_hours_container').hide();
                $("#rp_id").on('change', function(){
                    if($(this).val() == "new" && $(this).is(":visible")){
                        $("#cond-select-hidden").show();
                    } else {
                        $("#cond-select-hidden").hide();
                    }
                });
                $(".expand-click").click(function(){
                    $(".cond-hidden").hide();
                    $(this).siblings().show();
                    $("#cond-select-hidden").hide();
                    $("#rp_id").trigger("change");
                });
                $("#hours_custom").click(function() {
                    $('#custom_hours_container').show();
                });
                $("#help-click").click(function(){
                    $('#help-text').slideToggle('slow');
                });
                $(".cond-hidden").hide();
                $("#cond-select-hidden").hide();
                $("#category").hide();
                $("#help-text").hide();
                $(".expand-click :input[value='persoon']").click();
                $("#newcat").click(function(){
                    $("#category").show();
                });

                $('[data-toggle="tooltip"]').tooltip();
            });
        </script>
        <div class="row">
            <div class="col-md-12 well">
                <h4 id="help-click" data-collapsed-icon="arrow-d" data-expanded-icon="arrow-u"><i class="fa fa-arrow-circle-o-down" aria-hidden="true"></i> Hoe werkt deze pagina?</h4>
                <div id="help-text">
                    <ol>
                        <li>Kies een datum waarop je de activiteit hebt uitgevoerd. Deze mag niet in de toekomst liggen.</li>
                        <li>Vul een omschrijving in van de activiteit die je hebt uitgevoerd.</li>
                        <li>Geef aan hoeveel tijd je aan deze activiteit hebt besteed.</li>
                        <li>Selecteer de categorie waarin deze activiteit het beste past.</li>
                        <li>Selecteer welke hulpbron je hebt ingeschakeld bij deze activiteit.</li>
                        <li>Selecteer de status van deze werkzaamheid. Is deze al afgerond of ben je er nog mee bezig? Of is deze nog niet klaar maar heb je het overgedragen aan iemand anders?</li>
                        <li>Selecteer hoe moeilijk je deze taak vond. Liep je tegen problemen aan of ging het je goed af?</li>
                        <li>Klik op 'Opslaan'. De activiteit wordt onder in het scherm toegevoegd.</li>
                    </ol>
                </div>
            </div>
        </div>
        @if(Auth::user()->getCurrentWorkplaceLearningPeriod() == NULL)
            <div class="row">
                <div class="col-lg-12">
                    <div class="alert alert-notice">
                        <span>{{ Lang::get('elements.alerts.notice') }}: </span>{!! str_replace('%s', route('profile'), Lang::get('dashboard.nointernshipactive')) !!}
                    </div>
                </div>
            </div>
        @endif
        <div class="row">
            {!! Form::open(array('id' => 'taskForm', 'class' => 'form-horizontal well', 'url' => route('process-producing-create'))) !!}
                <div class="col-md-2 form-group">
                    <h4>Activiteit</h4>

                    <div class='input-group date fit-bs' id='date-deadline'>
                        <input id="datum" name="datum" type='text' class="form-control" value="{{ (!is_null(old('datum'))) ? date('d-m-Y', strtotime(old('datum'))) : date('d-m-Y') }}"/>
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>

                    <h5>Omschrijving:</h5>
                    <textarea class="form-control fit-bs" name="omschrijving" required maxlength="80" rows="5" cols="19"></textarea>

                    <h5>Koppel aan vorige activiteit:</h5>
                    <select class="form-control fit-bs" name="previous_wzh" >
                        <option value="-1">- Niet Koppelen-</option>
                        @if(Auth::user()->getCurrentWorkplaceLearningPeriod() != NULL)
                            @foreach(Auth::user()->getCurrentWorkplaceLearningPeriod()->getUnfinishedActivityProducing() as $w)
                                @if($w->nextLearningActivityProducing === null)
                                    {{-- Only allow to chain activity if it hasn't been chained yet --}}
                                <option value="{{ $w->lap_id }}">{{ date('d-m', strtotime($w->date)) ." - ".$w->description }}</option>
                                @endif
                            @endforeach
                        @endif
                    </select>

                </div>
                <div class="col-md-2 form-group buttons numpad">
                    <h4>Uren</h4>
                    <label><input type="radio" name="aantaluren" value="0.25" checked><span>15 min.</span></label>
                    <label><input type="radio" name="aantaluren" value="0.50"><span>30 min.</span></label>
                    <label><input type="radio" name="aantaluren" value="0.75"><span>45 min.</span></label>
                    @for($i = 1; $i <= 6; $i++)
                        {!! "<label>". Form::radio('aantaluren', $i) ."<span>". $i ." ". Lang::choice('elements.tasks.hour', $i) ."</span></label>" !!}
                    @endfor
                    <div class="custom">
                        <label id="hours_custom"><input type="radio" name="aantaluren" value="x" /><span>Anders</span></label>
                        <br/>
                        <div id="custom_hours_container"><input class="form-control" type="number" step="1" min="1" max="300" name="aantaluren_custom" value="5">minuten</div>
                    </div>
                </div>

                <div class="col-md-2 form-group buttons">
                    <h4>Categorie <i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="{{ trans('tooltips.producing_category') }}"></i></h4>
                    @if(Auth::user()->getCurrentWorkplaceLearningPeriod() != null)
                        @foreach($categories as $cat)
                            <label><input type="radio" name="category_id" value="{{ $cat->category_id }}" {{ ($cat->category_id == 1) ? "checked" : "" }}/><span>{{ $cat->category_label }}</span></label>
                        @endforeach
                    @endif
                    <div>
                        <label class="newcat"><input type="radio" name="category_id" value="new" /><span class="new" id="newcat">Anders<br />(Toevoegen)</span></label>
                        <input id="category" type="text" maxlength="50" name="newcat" placeholder="Omschrijving" />
                    </div>
                </div>
                <div class="col-md-2 form-group buttons">
                    <h4>Werken/Leren Met <i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="{{ trans('tooltips.producing_with') }}"></i></h4>
                    <div id="swvcontainer">
                        <label class="expand-click"><input type="radio" name="resource" value="persoon" checked/><span>Persoon</span></label>
                        <select id="rp_id" name="personsource" class="cond-hidden">
                            @foreach($learningWith as $res)
                                <option value="{{ $res->rp_id }}">{{ $res->person_label }}</option>
                            @endforeach */ ?>
                            <option value="new">Nieuw/Anders</option>
                        </select>
                        <input id="cond-select-hidden" type="text" maxlength="50" name="newswv" placeholder="Omschrijving" />
                    </div>
                    <div id="solocontainer">
                        <label class="expand-click"><input type="radio" name="resource" value="alleen" /><span>Alleen</span></label>
                    </div>
                    <div id="internetcontainer">
                        <label class="expand-click"><input type="radio" name="resource" value="internet" /><span>Internetbron</span></label>
                        <input class="cond-hidden" type="text" name="internetsource" value="" placeholder="http://www.bron.domein/" />
                    </div>
                    <div id="boekcontainer">
                        <label class="expand-click"><input type="radio" name="resource" value="boek" /><span>Boek/Artikel</span></label>
                        <input class="cond-hidden" type="text" name="booksource" value="" placeholder="Naam Boek/Artikel" />
                    </div>
                </div>
                <div class="col-md-2 form-group buttons">
                    <h4>Status <i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="{{ trans('tooltips.producing_status') }}"></i></h4>
                    <label><input type="radio" name="status" value="1" checked/><span>Afgerond</span></label>
                    <label><input type="radio" name="status" value="2"/><span>Mee Bezig</span></label>
                    <label><input type="radio" name="status" value="3"/><span>Overgedragen</span></label>
                </div>
                <div class="col-md-1 form-group buttons">
                    <h4>Moeilijkheidsgraad <i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="{{ trans('tooltips.producing_difficulty') }}"></i></h4>
                    <label><input type="radio" name="moeilijkheid" value="1" checked/><span>Makkelijk</span></label>
                    <label><input type="radio" name="moeilijkheid" value="2"/><span>Gemiddeld</span></label>
                    <label><input type="radio" name="moeilijkheid" value="3"/><span>Moeilijk</span></label>
                </div>
                <div class="col-md-1 form-group buttons">
                    <input type="submit" class="btn btn-info" style="margin: 44px 0 0 30px;" value="Save" />
                </div>
            {{ Form::close() }}
        </div>

        <div class="row">
            <script>
                window.activities = {!! $activitiesJson !!};
                window.exportTranslatedFieldMapping = {!! $exportTranslatedFieldMapping !!};
            </script>

            <div id="ActivityProducingProcessTable" class="__reactRoot col-md-12"></div>
        </div>


        {{--<div class="row">
            <table class="table blockTable col-md-12">
                <thead class="blue_tile">
                <tr>
                    <td>Datum</td>
                    <td>Omschrijving</td>
                    <td>Tijd (Uren)</td>
                    <td>Werken/leren met</td>
                    <td>Complexiteit</td>
                    <td></td>
                </tr>
                </thead>
                <tbody>
                @if(Auth::user()->getCurrentWorkplace() && Auth::user()->getCurrentWorkplaceLearningPeriod()->hasLoggedHours())
                    @foreach(Auth::user()->getCurrentWorkplaceLearningPeriod()->getLastActivity(8) as $a)
                        <tr>
                            <td>{{ date('d-m', strtotime($a->date)) }}</td>
                            <td>{{ $a->description }}</td>
                            <td>{{ $a->getDurationString() }}</td>
                            <td>{{ $a->getResourceDetail() }}</td>
                            <td>{{ $a->getDifficulty() }}</td>
                            <td><a href="{{route('process-producing-edit', ['id' => $a->lap_id]) }}"><i class="glyphicon glyphicon-pencil" aria-hidden="true"></i></a></td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>
        </div>--}}
    </div>
    <script type="text/javascript">
        $(document).ready(function () {
            $('#date-deadline').datetimepicker({
                locale: 'nl',
                format: 'DD-MM-YYYY',
                minDate: "{{ date('Y-m-d', strtotime("-3 week")) }}",
                maxDate: "{{ date('Y-m-d', strtotime("now")) }}",
                useCurrent: false,
            });
        }).on('dp.change', function(e) {
            $('#datum').attr('value', moment(e.date).format("DD-MM-YYYY"));
        });
    </script>
@stop
