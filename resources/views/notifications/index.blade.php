@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">{{ __('Notificaciones') }}</div>

                <div class="card-body">
                    <ul class="list-group">
                        @forelse ($notifications as $notification)
                            <li class="list-group-item d-flex justify-content-between align-items-center @if(!$notification->read_at) bg-light @endif">
                                <div>
                                    <strong class="text-primary">{{ $notification->data['title'] }}</strong>
                                    <p class="mb-0">{{ $notification->data['message'] }}</p>
                                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                </div>
                                @if(!$notification->read_at)
                                    <a href="{{ route('notifications.read', $notification->id) }}" class="btn btn-sm btn-outline-primary" title="Marcar como leída">
                                        <i class="fas fa-check"></i>
                                    </a>
                                @endif
                            </li>
                        @empty
                            <li class="list-group-item text-center">No tienes notificaciones.</li>
                        @endforelse
                    </ul>
                </div>

                <div class="card-footer">
                    {{ $notifications->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
