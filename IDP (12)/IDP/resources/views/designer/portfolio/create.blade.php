@extends('layouts.app')

@section('title','Add Portfolio')

@section('content')

<div class="max-w-3xl mx-auto px-6 py-10">

<h2 class="text-2xl font-bold mb-6">Add Portfolio</h2>

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
      action="{{ route('designer.portfolio.store') }}"
      enctype="multipart/form-data"
      class="bg-white p-6 rounded-xl shadow space-y-6">

@csrf


<div>
<label class="block font-medium mb-2">Title</label>
<input type="text"
       name="title"
       value="{{ old('title') }}"
       class="w-full border rounded-lg px-4 py-2"
       required>
</div>


<div>
<label class="block font-medium mb-2">Category</label>
<input type="text"
       name="category"
       value="{{ old('category') }}"
       placeholder="Example: Living Room, Kitchen, Office"
       class="w-full border rounded-lg px-4 py-2">
</div>


<div>
<label class="block font-medium mb-2">Description</label>
<textarea name="description"
          rows="4"
          class="w-full border rounded-lg px-4 py-2"
          placeholder="Describe this design project...">{{ old('description') }}</textarea>
</div>


<div>
<label class="block font-medium mb-2">Portfolio Images</label>

<input type="file"
       name="images[]"
       multiple
       accept="image/*"
       class="w-full border rounded-lg px-4 py-2 cursor-pointer bg-gray-50">

<p class="text-xs text-gray-500 mt-2">
You can upload multiple images (jpg, png, webp). Max size: 2MB each.
</p>

</div>


<div class="flex gap-3 pt-3">

<button
onclick="this.disabled=true;this.form.submit();"
class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
Create Portfolio
</button>

<a href="{{ route('designer.portfolio.index') }}"
class="px-6 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition">
Cancel
</a>

</div>

</form>

</div>

@endsection
