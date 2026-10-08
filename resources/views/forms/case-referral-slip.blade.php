<flux:modal name="case-referral-slip-modal" class="w-[95vw] max-w-7xl">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Case Referral Slip</flux:heading>
            <flux:subheading>Preview the document before assigning it to a student.</flux:subheading>
        </div>
        <div class="h-[75vh] w-full overflow-hidden rounded-lg border">
            <iframe src="{{ asset('slips/CASE-REFERRAL-SLIP.pdf') }}" title="Case Referral Slip" class="h-full w-full border-none"></iframe>
        </div>
        <div class="flex justify-end gap-2">
            <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
            <a href="{{ route('slips.fillup', 'case-referral-slip') }}" class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white">            Assign to student</a>
        </div>
    </div>
</flux:modal>
