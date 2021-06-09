<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <table class="table table-sm">
                    <thead>
                        <tr class="table-primary">
                            <th scope="col">User</th>
                            <th scope="col">Action</th>
                            <th scope="col">When</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                        <tr>
                            <td>{{ $log->user->name }}</td>
                            <td>{{ $log->action }}</td>
                            <td>{{ $log->created_at->diffForHumans() }}</td>
                        </tr>
                        @endforeach


                    </tbody>
                </table>
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</x-app-layout>