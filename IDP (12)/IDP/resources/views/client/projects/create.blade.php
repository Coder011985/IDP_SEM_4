@extends('layouts.client')

@section('title','Create Project')

@section('content')

<div class="max-w-4xl mx-auto">

<h2 class="text-2xl font-bold mb-6 text-slate-800">
Create New Project
</h2>


@if($errors->any())

<div class="mb-6 p-4 bg-red-100 text-red-700 rounded-lg">

<ul class="list-disc pl-5">

@foreach($errors->all() as $error)

<li>{{ $error }}</li>

@endforeach

</ul>

</div>

@endif



<form method="POST"
action="{{ route('client.projects.store') }}"
class="space-y-6 bg-white p-8 rounded-xl shadow">

@csrf



{{-- SMART HIRE --}}
@if(isset($preselectedDesigner) && $preselectedDesigner)

<div class="p-4 bg-blue-50 border rounded-lg">

<p class="text-sm text-gray-600">
You are hiring:
</p>

<p class="font-semibold text-lg">
{{ $preselectedDesigner->name }}
</p>

<p class="text-xs text-gray-500">
{{ $preselectedDesigner->specialization ?? 'Interior Designer' }}
</p>

<input type="hidden"
name="preselected_designer_id"
value="{{ $preselectedDesigner->id }}">

</div>

@endif



{{-- TITLE --}}
<div>

<label class="block mb-1 font-medium">
Title
</label>

<input
type="text"
name="title"
value="{{ old('title') }}"
class="w-full border rounded-lg px-4 py-2"
required>

</div>



{{-- DESCRIPTION --}}
<div>

<label class="block mb-1 font-medium">
Description
</label>

<textarea
name="description"
rows="4"
class="w-full border rounded-lg px-4 py-2"
required>{{ old('description') }}</textarea>

</div>



{{-- BUDGET --}}
<div class="grid grid-cols-2 gap-4">

<div>

<label class="block mb-1 font-medium">
Budget Min
</label>

<input
type="number"
name="budget_min"
value="{{ old('budget_min') }}"
class="w-full border rounded-lg px-4 py-2"
required>

</div>


<div>

<label class="block mb-1 font-medium">
Budget Max
</label>

<input
type="number"
name="budget_max"
value="{{ old('budget_max') }}"
class="w-full border rounded-lg px-4 py-2"
required>

</div>

</div>



{{-- TYPE --}}
<div>

<label class="block mb-1 font-medium">
Project Type
</label>

<input
type="text"
name="type"
value="{{ old('type') }}"
class="w-full border rounded-lg px-4 py-2"
required>

</div>



{{-- PROPERTY TYPE --}}
<div>

<label class="block mb-1 font-medium">
Property Type
</label>

<select
name="property_type"
class="w-full border rounded-lg px-4 py-2"
required>

<option value="">Select</option>

<option value="residential" {{ old('property_type')=='residential'?'selected':'' }}>
Residential
</option>

<option value="commercial" {{ old('property_type')=='commercial'?'selected':'' }}>
Commercial
</option>

<option value="office" {{ old('property_type')=='office'?'selected':'' }}>
Office
</option>

<option value="luxury" {{ old('property_type')=='luxury'?'selected':'' }}>
Luxury
</option>

</select>

</div>



{{-- LOCATION --}}
<div>

<label class="block mb-1 font-medium">
Location
</label>

<input
type="text"
name="location"
value="{{ old('location') }}"
class="w-full border rounded-lg px-4 py-2">

</div>



{{-- AREA --}}
<div>

<label class="block mb-1 font-medium">
Area (sq.ft)
</label>

<input
type="number"
name="area_sqft"
value="{{ old('area_sqft') }}"
class="w-full border rounded-lg px-4 py-2">

</div>



{{-- DEADLINE --}}
<div>

<label class="block mb-1 font-medium">
Deadline
</label>

<input
type="date"
name="deadline"
value="{{ old('deadline') }}"
class="w-full border rounded-lg px-4 py-2">

</div>



{{-- STYLE --}}
<div>

<label class="block mb-1 font-medium">
Design Style
</label>

<input
type="text"
name="style"
value="{{ old('style') }}"
class="w-full border rounded-lg px-4 py-2">

</div>



{{-- REQUIREMENTS --}}
<div>

<label class="block mb-1 font-medium">
Extra Requirements
</label>

<textarea
name="requirements"
rows="3"
class="w-full border rounded-lg px-4 py-2">{{ old('requirements') }}</textarea>

</div>



<button
type="submit"
class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">

Create Project

</button>


</form>

</div>

@endsection
