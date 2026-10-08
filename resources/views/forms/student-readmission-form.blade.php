<flux:modal name="student-readmission-form-modal" class="w-[95vw] max-w-7xl">
    <div class="space-y-6">
        <div><flux:heading size="lg">Student Readmission Slip</flux:heading><flux:subheading>Preview the document before assigning it to a student.</flux:subheading></div>
        <div class="h-[75vh] w-full overflow-hidden rounded-lg border"><iframe src="{{ asset('slips/STUDENT-READMISSION-FORM.pdf') }}" title="Student Readmission Slip" class="h-full w-full border-none"></iframe></div>
        <div class="flex justify-end gap-2">
            <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
            <a href="{{ route('slips.fillup', 'student-readmission-form') }}" class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white">Assign to student</a>
        </div>
    </div>
</flux:modal>
