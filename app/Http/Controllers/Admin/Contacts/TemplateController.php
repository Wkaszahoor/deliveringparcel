<?php

namespace App\Http\Controllers\Admin\Contacts;

use App\Http\Controllers\Controller;
use App\Models\ContactReplyTemplate;
use App\Models\Contactus;
use Illuminate\Http\Request;

/**
 * Agent D — reply templates admin-lite CRUD.
 *
 * index  : list of templates
 * create / store : new template
 * edit   / update : existing template
 * destroy: delete (data-dp-confirm)
 * show   : JSON body of one template (used by the contacts reply composer)
 */
class TemplateController extends Controller
{
    public function index()
    {
        $templates = ContactReplyTemplate::orderBy('name')->get();

        return view('admin.contacts.templates.index', compact('templates'));
    }

    public function create()
    {
        $template = new ContactReplyTemplate(['is_active' => true]);

        return view('admin.contacts.templates.form', compact('template'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $template = ContactReplyTemplate::create($validated + ['is_active' => (bool) $request->boolean('is_active')]);

        return redirect()->route('admin.contacts.templates.index')
            ->with('success', "Template '{$template->name}' created.");
    }

    public function edit($id)
    {
        $template = ContactReplyTemplate::findOrFail($id);

        return view('admin.contacts.templates.form', compact('template'));
    }

    public function update(Request $request, $id)
    {
        $template = ContactReplyTemplate::findOrFail($id);

        $validated = $request->validate($this->rules());

        $template->update($validated + ['is_active' => (bool) $request->boolean('is_active')]);

        return redirect()->route('admin.contacts.templates.index')
            ->with('success', "Template '{$template->name}' updated.");
    }

    public function destroy(Request $request, $id)
    {
        $template = ContactReplyTemplate::findOrFail($id);
        $name = $template->name;
        $template->delete();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => "Template '{$name}' deleted."]);
        }

        return redirect()->route('admin.contacts.templates.index')->with('success', "Template '{$name}' deleted.");
    }

    /**
     * GET /admin/contact-templates/{template} — JSON for the reply composer:
     * returns the template with placeholders already rendered for a contact.
     */
    public function show(Request $request, $id)
    {
        $template = ContactReplyTemplate::findOrFail($id);

        $body = $template->body;
        $subject = $template->subject;

        if ($contactId = (int) $request->input('contact_id')) {
            $contact = Contactus::find($contactId);
            if ($contact) {
                $body = ContactReplyTemplate::renderBody($body, $contact);
                $subject = ContactReplyTemplate::renderBody($subject, $contact);
            }
        }

        return response()->json([
            'id' => $template->id,
            'name' => $template->name,
            'subject' => $subject,
            'body' => $body,
            'placeholders' => ['{name}', '{email}', '{message}', '{company}'],
        ]);
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:191',
            'subject' => 'required|string|max:191',
            'body' => 'required|string|max:6000',
        ];
    }
}
