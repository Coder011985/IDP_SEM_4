<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Admin Dashboard
        </h2>
    </x-slot>

    <div class="p-6">
        <div class="grid grid-cols-3 gap-6">

            <div class="bg-blue-500 text-white p-6 rounded-xl shadow">
                Total Projects: 10
            </div>

            <div class="bg-green-500 text-white p-6 rounded-xl shadow">
                Active Designers: 5
            </div>

            <div class="bg-red-500 text-white p-6 rounded-xl shadow">
                Pending Requests: 3
            </div>

        </div>
    </div>

</x-app-layout>
