@extends('layouts.app')


@extends('layouts.sidebar')


@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Modifier le produit') }}</div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3 row">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Nom') }}</label>
                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $product->name) }}" required autocomplete="name" autofocus>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="image" class="col-md-4 col-form-label text-md-end">{{ __('Image') }}</label>
                            <div class="col-md-6">
                                @if($product->image_path)
                                    <div class="mb-2">
                                        <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}" style="max-width: 100px; max-height: 100px;">
                                    </div>
                                @endif
                                <input id="image" type="file" class="form-control @error('image') is-invalid @enderror" name="image">
                                <small class="form-text text-muted">Laissez vide pour conserver l'image actuelle. Formats acceptés: jpeg, png, jpg, gif.</small>
                                @error('image')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Mettre à jour') }}
                                </button>
                                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                                    {{ __('Annuler') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection