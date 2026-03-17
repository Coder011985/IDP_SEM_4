@extends('layouts.client')

@section('title','Client Dashboard')

@section('content')

<div class="flex justify-between items-center mb-8">

    <h1 class="text-2xl font-bold">
        Client Dashboard
    </h1>

    <a href="{{ route('client.projects.create') }}"
       class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition">

        + Create Project

    </a>

</div>


{{-- ===================== --}}
{{-- STATISTICS --}}
{{-- ===================== --}}

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">

    <div class="bg-white p-6 rounded-xl shadow border">

        <p class="text-gray-500 text-sm">
            Total Projects
        </p>

        <h2 class="text-3xl font-bold mt-2">
            {{ $total ?? 0 }}
        </h2>

    </div>


    <div class="bg-white p-6 rounded-xl shadow border">

        <p class="text-gray-500 text-sm">
            Pending Projects
        </p>

        <h2 class="text-3xl font-bold mt-2">
            {{ $pending ?? 0 }}
        </h2>

    </div>


    <div class="bg-white p-6 rounded-xl shadow border">

        <p class="text-gray-500 text-sm">
            Completed Projects
        </p>

        <h2 class="text-3xl font-bold mt-2">
            {{ $completed ?? 0 }}
        </h2>

    </div>

</div>



{{-- ===================== --}}
{{-- RECENT PROJECTS --}}
{{-- ===================== --}}

<div class="bg-white rounded-xl shadow border">

    <div class="p-6 border-b">

        <h2 class="text-lg font-semibold">
            Recent Projects
        </h2>

    </div>


    <div class="overflow-x-auto">

        <table class="w-full text-left">

            <thead class="bg-gray-50 text-sm text-gray-600">

                <tr>
                    <th class="p-4">Title</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Budget</th>
                    <th class="p-4">Deadline</th>
                    <th class="p-4">Action</th>
                </tr>

            </thead>


            <tbody>

            @forelse($recentProjects as $project)

                <tr class="border-t">

                    <td class="p-4 font-medium">
                        {{ $project->title }}
                    </td>


                    <td class="p-4">

                        <span class="px-3 py-1 text-sm rounded bg-gray-100">
                            {{ ucfirst($project->status) }}
                        </span>

                    </td>


                    <td class="p-4">
                        ₹{{ number_format($project->budget_min ?? 0) }} -
                        ₹{{ number_format($project->budget_max ?? 0) }}
                    </td>


                    <td class="p-4">
                        {{ $project->deadline ? $project->deadline->format('d M Y') : '-' }}
                    </td>


                    <td class="p-4">

                        <a href="{{ route('client.projects.show', $project->id) }}"
                           class="text-blue-600 hover:underline">

                           View

                        </a>

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="5" class="p-6 text-center text-gray-500">

                        No projects found

                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection
