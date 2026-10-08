<flux:modal name="qf-bo-oss-04-client-information-form-modal" class="w-[95vw] max-w-7xl">
    <div class="space-y-6">
        <div><flux:heading size="lg">Client Information Form</flux:heading><flux:subheading>Issue only to a student visiting the Guidance Office for the first time.</flux:subheading></div>
        <div class="h-[75vh] w-full overflow-hidden rounded-lg border"><iframe src="{{ asset('slips/QF-BO-OSS-04-Client-Information-Form.pdf') }}" title="Client Information Form" class="h-full w-full border-none"></iframe></div>
        <div class="flex justify-end gap-2">
            <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
            <a href="{{ route('slips.fillup', 'qf-bo-oss-04-client-information-form') }}" class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white">Issue to student</a>
        </div>
    </div>
</flux:modal>
