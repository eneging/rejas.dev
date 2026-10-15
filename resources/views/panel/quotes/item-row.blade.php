<div class="grid gap-3 sm:grid-cols-[1fr_7rem_9rem_auto]" data-row>
    <input name="items[{{ $i }}][description]" value="{{ $item['description'] }}" placeholder="Descripción" required class="input" aria-label="Descripción">
    <input name="items[{{ $i }}][quantity]" value="{{ $item['quantity'] }}" type="number" step="0.01" min="0.01" required class="input" data-qty aria-label="Cantidad">
    <input name="items[{{ $i }}][unit_price]" value="{{ $item['unit_price'] }}" type="number" step="0.01" min="0" placeholder="Precio unit." required class="input" data-price aria-label="Precio unitario">
    <button type="button" data-repeater-remove class="btn-secondary btn-sm" aria-label="Quitar partida">✕</button>
</div>
