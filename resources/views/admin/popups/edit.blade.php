@extends('admin.layouts.app')

@section('title', 'Modifier une popup')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Modifier la popup</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.popups.index') }}">Popups</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <span>{{ $popup->title }}</span>
            </li>
        </ul>
    </div>

    <form action="{{ route('admin.popups.update', $popup) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Veuillez corriger les erreurs suivantes :</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @include('admin.popups._form')
    </form>
</div>
@endsection

