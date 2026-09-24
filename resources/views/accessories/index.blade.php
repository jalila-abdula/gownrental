<x-app-layout>
    <div class="sb-page sb-inventory-page"><div class="sb-wrap">
        <div class="sb-heading"><div><span class="sb-kicker">INVENTORY · STYLING PIECES</span><h1>Gown <em>accessories.</em></h1><p>Track the smaller pieces that complete a rental look.</p></div><a class="sb-inventory-primary" href="{{ route('owner.accessories.create') }}">Add accessory <span>＋</span></a></div>
        @if(session('success'))<div class="sb-success">{{ session('success') }}</div>@endif
        <div class="sb-inventory-note"><span>✦</span><div><b>What are accessories?</b>Accessories are separate stock items such as shawls, belts, gloves, or hairpieces. Link an accessory to a gown from the gown’s edit page to show that it is included with the look. This list tracks stock and replacement cost.</div></div>
        <section class="sb-panel"><div class="sb-inventory-content">
            <div class="sb-inventory-toolbar"><div><h2>Accessory stock</h2><p>{{ $accessories->count() }} items</p></div></div>
            <div class="sb-inventory-table-wrap"><table class="sb-inventory-table"><thead><tr><th>Accessory</th><th>Stock</th><th>Linked gowns</th><th>Replacement cost</th><th>Status</th><th></th></tr></thead><tbody>
                @forelse($accessories as $accessory)
                    <tr><td><div class="sb-inventory-name"><div class="sb-inventory-thumb">✧</div><span><b>{{ $accessory->name }}</b><small>{{ $accessory->description ?: 'No description added' }}</small></span></div></td><td>{{ $accessory->quantity }}</td><td>{{ $accessory->gowns_count }}</td><td>₱{{ number_format($accessory->replacement_cost,2) }}</td><td><span class="sb-inventory-status {{ in_array($accessory->status,['damaged','unavailable']) ? 'is-damaged' : '' }}">{{ ucfirst($accessory->status) }}</span></td><td><div class="sb-inventory-actions"><a href="{{ route('owner.accessories.show',$accessory) }}">View</a><a href="{{ route('owner.accessories.edit',$accessory) }}">Edit</a>@if($accessory->status === 'available')<form method="POST" action="{{ route('owner.accessories.destroy',$accessory) }}" onsubmit="return confirm('Mark this accessory unavailable?')">@csrf @method('DELETE')<button class="sb-retire">Disable</button></form>@endif</div></td></tr>
                @empty
                    <tr><td colspan="6"><div class="sb-inventory-empty"><b>No accessories added</b><p>Add included styling pieces and link them to gowns when editing a gown.</p><a class="sb-inventory-primary" href="{{ route('owner.accessories.create') }}">Add an accessory</a></div></td></tr>
                @endforelse
            </tbody></table></div>
        </div></section>
    </div></div>
</x-app-layout>
