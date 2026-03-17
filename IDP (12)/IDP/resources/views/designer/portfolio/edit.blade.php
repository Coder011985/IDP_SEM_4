@extends('layouts.designer')

@section('title','Edit Portfolio')

@section('content')

<div class="max-w-3xl mx-auto">

<h2 class="text-2xl font-bold mb-6">Edit Portfolio</h2>

@if ($errors->any())
<div class="bg-red-100 text-red-700 p-3 rounded mb-4">
<ul class="list-disc pl-5 text-sm">
@foreach ($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif

<form method="POST"
      action="{{ route('designer.portfolio.update',$portfolio->id) }}"
      enctype="multipart/form-data"
      class="bg-white p-6 rounded-xl shadow space-y-5">

@csrf
@method('PUT')


<div>
<label class="block font-medium mb-1">Title</label>
<input type="text"
       name="title"
       value="{{ $portfolio->title }}"
       class="w-full border rounded-lg px-3 py-2"
       required>
</div>


<div>
<label class="block font-medium mb-1">Category</label>
<input type="text"
       name="category"
       value="{{ $portfolio->category }}"
       class="w-full border rounded-lg px-3 py-2">
</div>


<div>
<label class="block font-medium mb-1">Description</label>
<textarea name="description"
          rows="4"
          class="w-full border rounded-lg px-3 py-2">{{ $portfolio->description }}</textarea>
</div>


<div>
<label class="block font-medium mb-1">Add More Images</label>
<input type="file"
       name="images[]"
       multiple
       accept="image/*"
       class="w-full border rounded-lg px-3 py-2 cursor-pointer bg-gray-50">

<p class="text-xs text-gray-500 mt-1">
You can upload additional images to this portfolio.
</p>
</div>


@if($portfolio->images->count())

<div>
<p class="font-medium mb-2">Existing Images</p>

<div class="grid grid-cols-3 gap-4">

@foreach($portfolio->images as $img)

<img src="{{ asset('storage/'.$img->image_path) }}"
class="h-32 w-full object-cover rounded">

@endforeach

</div>

</div>

@endif


<div class="flex gap-3">

<button
onclick="this.disabled=true;this.form.submit();"
class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
Update Portfolio
</button>

<a href="{{ route('designer.portfolio.index') }}"
class="px-6 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">
Cancel
</a>

</div>

</form>

</div>

@endsection
