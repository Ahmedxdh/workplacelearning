<?php
/**
 * This file (profile.blade.php) was created on 06/19/2016 at 16:17.
 * (C) Max Cassee
 * This project was commissioned by HU University of Applied Sciences.
 */
?>
@extends('layout.HUdefault')
@section('title')
    {{ __('saved_learning_items.saved-items') }}
@stop
@section('content')
<?php
use App\Student;
use App\SavedLearningItem
/** @var Student $student */;
/** @var SavedLearningItem $sli */
/** @var Folder $folder */?>

    <div class="container-fluid">
        <script>
            $(document).ready(function () {
                // Tooltips
                $('[data-toggle="tooltip"]').tooltip();
            });
        </script>
        @card
        <h1>{{ __('saved_learning_items.saved') }}</h1>
        <div class="row">
            <div class="col-md-12">
                @card
                    <h2 class='maps'>{{ __('saved_learning_items.timeline') }}</h2>
                    <br>
                    @foreach($sli as $item)
                    <!-- Tips -->
                        @if($item->category === 'tip')
                            @card
                            <h4 class="maps">{{date('d-m-Y', strtotime($item->created_at))}}</h4>
                            <div class="alert" style="background-color: #00A1E2; color: white; margin-left:2px; margin-bottom: 10px" role="alert">
                            <h4 class="tip-title">{{ __('tips.personal-tip') }}</h4>
                            <a href="{{ route('saved-learning-items-delete', ['sli' => $item])}}"><span class="glyphicon glyphicon-trash delete-tip" aria-hidden="true"></span></a>
                            <a onclick="chooseItem({{ $item->sli_id }})" data-target="#addItemModel" data-toggle="modal"><span class="glyphicon glyphicon-plus add-tip" aria-hidden="true"></span></a>
                            <h4 class="tip-title">{{ __('tips.personal-tip') }}</h4>
                                @if (in_array($item->item_id, array_keys($evaluatedTips)))
                                    <p>{{$evaluatedTips[$item->item_id]->getTipText()}}</p>
                                @else
                                    <p>{{ __('saved_learning_items.tip-not-found') }}</p>
                                @endif
                            </div>
                            @endcard
                        @endif
                        <!-- Activities -->
                        @if ($item->category === 'activity' && in_array($item->item_id, array_keys($activities)))
                            @card
                            <h4 class="maps">{{date('d-m-Y', strtotime($item->created_at))}}</h4>
                            <div class="alert" style="background-color: #FFFFFF; color: 00A1E2; margin-left:2px; margin-bottom: 10px; border: 1px solid #00A1E2" role="alert">
                            <a href="{{ route('saved-learning-items-delete', ['sli' => $item])}}"><span class="glyphicon glyphicon-trash delete-tip" aria-hidden="true"></span></a>
                            <a onclick="chooseItem({{ $item->sli_id }})" data-target="#addItemModel" data-toggle="modal"><span class="glyphicon glyphicon-plus add-tip" aria-hidden="true"></span></a>
                                <h4>Activiteit</h4>
                                <p><strong>{{date('d-m-Y', strtotime($activities[$item->item_id]->date))}}</strong>: {{$activities[$item->item_id]->description}}</p>
                                <span class="glyphicon glyphicon-time activity_icons" aria-hidden="true"></span>{{$activities[$item->item_id]->duration}} uur
                                @if($activities[$item->item_id]->res_person_id === null) 
                                    <br><span class="glyphicon glyphicon-user activity_icons" aria-hidden="true"></span>Alleen
                                @else
                                <br><span class="glyphicon glyphicon-user activity_icons" aria-hidden="true"></span>{{$resourcePerson[$item->item_id]->person_label}} 
                                @endif
                                <br><span class="glyphicon glyphicon-tag activity_icons" aria-hidden="true"></span>{{$categories[$item->item_id]->category_label}} 
                            </div>
                            @endcard
                        @endif
                        
                        @endforeach

            </div>
         </div>
    </div>
    @endcard
    <!-- Modal to add a item to folder-->
    <div class="modal fade" id="addItemModel" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">{{ __('folder.add-to-folder') }}</h4>
        </div>
        <div class="modal-body">

        {!! Form::open(array('url' =>  route('saved-learning-items-addItemToFolder'))) !!}

            <div class="form-group">
                <input type='text' name='sli_id' id="sl-item_id" class="form-control hidden_element">
            </div>

            <div class="form-group">
                <select name="chooseFolder" class="form-control">
                    @foreach($student->folders as $folder)
                        <option value="{{$folder->folder_id}}">{{$folder->title}}</option>
                    @endforeach
                </select>
            </div>

            </div>
            <div class="modal-footer">
                {{ Form::submit(__('general.save'), array('class' => 'btn btn-primary', 'id' => 'addItemToFolder')) }}
                {{ Form::close() }}
            </div>
      </div>
      
    </div>
    </div>

      <!-- Modal to add a folder-->
    <div class="modal fade" id="addFolderModel" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">{{ __('folder.new-folder') }}</h4>
        </div>
        <div class="modal-body">

        {!! Form::open(array('url' =>  route('folder.create'))) !!}
            <div class="form-group">
                <label>{{ __('folder.title') }}</label>
                <input id='folderTitle' type='text' name='folder_title' class="form-control" maxlength="100" required>
            </div>
                          
            <div class="form-group">
                <label>{{ __('folder.description') }}</label>
                <textarea type='text' name='folder_description' id="folderDescription" class="form-control" maxlength="255" required></textarea>
            </div>
            

        <div class="modal-footer">
            {{ Form::submit(__('general.save'), array('class' => 'btn btn-primary', 'id' => 'createFolderButton')) }}
            {{ Form::close() }}
        </div>
      </div>
      
    </div>
  </div>
  
</div>

{{-- Modal to add items to a folder from the 'guidance' page --}}
<div class="modal fade" id="AddItemsToFolderModel" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                {{-- <h4 class="modal-title">{{ __('folder.add-items') }}</h4> --}}
                <h4 class="modal-title">Add Items to this folder.</h4>
            </div>
            <div class="modal-body">
                <div class="selected-folder-info">
                    <h4 id="folder-title"></h4>
                    <p id="folder-created-at"></p>
                </div>
                <h4 id="selected-items-count" class="right no-margin"></h4>
                {{-- <p>{{ __('folder.items-limit-msg') }}</p> --}}
                <p>Kies maximaal drie items om aan deze map toe te voegen.
                    <i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="{{ __('folder.items-limit-hint') }}"></i>
                </p>

                {!! Form::open(array('url' =>  route('folder.AddItemsToFolder'))) !!}
                <div class="form-group">
                    <input class="hidden_element" type='text' name="selected_folder_id" id="selected_folder_id" class="form-control">
                </div>

                <div class="ml-learning-items">
                    <h5>Learning items</h5>
                    @foreach ($sli as $item)
                        @if ($item->category === 'tip')
                            @card
                            <div class="form-group item">
                                <input type="checkbox" name="check_list[]" value="{{$item->sli_id}}" onclick="countSelectedItems()"/>
                                <div class="alert" style="background-color: #00A1E2; color: white;" role="alert">
                                    <h4 class="tip-title">{{ __('tips.personal-tip') }}</h4>
                                    @if (in_array($item->item_id, array_keys($evaluatedTips)))
                                        <p>{{$evaluatedTips[$item->item_id]->getTipText()}}</p>
                                    @else
                                        <p>{{ __('saved_learning_items.tip-not-found') }}</p>
                                    @endif
                                </div>
                            </div>
                            @endcard
                        @endif
                    @endforeach
                </div>
                    
                <div class="modal-footer">
                    {{ Form::submit(__('general.save'), array('class' => 'btn btn-primary', 'id' => 'addItemsButton')) }}
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@include('js.learningitem_save')
@stop