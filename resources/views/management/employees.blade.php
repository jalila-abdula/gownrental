<x-app-layout>
    <div class="sb-page"><div class="sb-wrap">
        <div class="sb-heading">
            <div><h1>Your <em>team.</em></h1><p>Manage employee accounts, access, and work activity.</p></div>
            <a class="sb-btn" href="{{ route('owner.employees.create') }}">Add employee</a>
        </div>
        @if(session('success'))<div class="sb-success">{{ session('success') }}</div>@endif
        <section class="sb-panel">
            <div class="sb-panel-head"><div><h2>Staff directory</h2><p>{{ $employees->total() }} employee accounts</p></div></div>
            <div class="sb-table-wrap"><table class="sb-table">
                <thead><tr><th>EMPLOYEE</th><th>CONTACT</th><th>POSITION / CODE</th><th>STATUS</th><th>ACCESS</th></tr></thead>
                <tbody>
                    @forelse($employees as $employee)
                        <tr onclick="if (!event.target.closest('a, button, form')) window.location.href='{{ route('owner.employees.show', $employee) }}'" style="cursor:pointer">
                            <td><a href="{{ route('owner.employees.show', $employee) }}" class="sb-text-link"><strong>{{ $employee->full_name }}</strong><small class="sb-cell-sub">View work log</small></a></td>
                            <td>{{ $employee->user->email }}<small class="sb-cell-sub">{{ $employee->contact_number }}</small></td>
                            <td>{{ $employee->position }}<small class="sb-cell-sub">{{ $employee->employee_code }}</small></td>
                            <td><span class="sb-status">{{ ucfirst($employee->status) }}</span></td>
                            <td><form method="POST" action="{{ route('owner.employees.toggle', $employee) }}">@csrf @method('PATCH')<button class="sb-small-btn {{ $employee->status === 'active' ? 'sb-deactivate' : '' }}">{{ $employee->status === 'active' ? 'Deactivate' : 'Activate' }}</button></form></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="sb-empty">No employees yet. Add the first staff account.</td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </section>
        <div class="sb-pagination">{{ $employees->links() }}</div>
    </div></div>
</x-app-layout>
