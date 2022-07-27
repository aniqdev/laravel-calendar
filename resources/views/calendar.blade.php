@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Events
                    <div class="buttons" style="float:right;">
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createEventModal">Create</button>
                        <a href="{{ route('calendar.update') }}" class="btn btn-secondary btn-sm">Update</a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="row">
                    @foreach($events as $event)
                        <div class="col-6">
                            <div class="card" style="height: 100%;">
                              <div class="card-body">
                                <h5 class="card-title">{{ $event->title }}</h5>
                                <h6 class="card-subtitle mb-2 text-muted">{{ $event->start }}</h6>
                                <p class="card-text">{{ $event->description }}</p>
                                <a href="{{ $event->link }}" target="_blank" class="card-link">Event link</a>
                              </div>
                            </div>
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Modal -->
<div class="modal fade" id="createEventModal" tabindex="-1" aria-labelledby="createEventModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="createEventModalLabel">Modal title</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="post" action="{{ route('events.add') }}" class="form">
            @csrf
            <div class="form-group">
                <label>Event Title</label>
                <input type="text" class="form-control" name="title" value="<?php echo !empty($postData['title'])?$postData['title']:''; ?>" required="">
            </div>
            <div class="form-group">
                <label>Event Description</label>
                <textarea name="description" class="form-control"><?php echo !empty($postData['description'])?$postData['description']:''; ?></textarea>
            </div>
            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" class="form-control" value="<?php echo !empty($postData['location'])?$postData['location']:''; ?>">
            </div>
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="date" class="form-control" value="<?php echo !empty($postData['date'])?$postData['date']:''; ?>" required="">
            </div>
            <div class="form-group row mb-4">
                <div class="col-6">
                    <label>Time</label>
                    <input type="time" name="time_from" class="form-control" value="<?php echo !empty($postData['time_from'])?$postData['time_from']:''; ?>">
                </div>
                <div class="col-6">
                    <span>To</span>
                    <input type="time" name="time_to" class="form-control" value="<?php echo !empty($postData['time_to'])?$postData['time_to']:''; ?>">
                </div>
            </div>
            <div class="form-group-">
                <input type="submit" class="form-control btn-primary" name="submit" value="Add Event"/>
            </div>
        </form>
      </div>
{{--       <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div> --}}
    </div>
  </div>
</div>
@endsection