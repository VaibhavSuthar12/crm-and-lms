<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Models\Activity;
use App\Models\Customer;
use App\Models\Lead;
use App\Services\ActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(private ActivityService $activityService) {}

    /**
     * List all customers.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Customer::with(['assignee:id,name', 'lead:id,title,status']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('company_name', 'like', "%{$request->search}%");
            });
        }

        return response()->json(
            $query->latest()->paginate($request->get('per_page', 15))
        );
    }

    /**
     * Convert a lead to customer.
     */
    public function convertLead(Request $request, Lead $lead): JsonResponse
    {
        if ($lead->customer) {
            return response()->json(['message' => 'This lead has already been converted.'], 422);
        }

        $customer = Customer::create([
            'lead_id'      => $lead->id,
            'first_name'   => $lead->first_name,
            'last_name'    => $lead->last_name,
            'email'        => $lead->email,
            'phone'        => $lead->phone,
            'company_name' => $lead->company,
            'assigned_to'  => $lead->assigned_to,
        ]);

        // Mark lead as Won
        $lead->update(['status' => Lead::STATUS_WON]);

        $this->activityService->log(
            user: $request->user(),
            type: Activity::TYPE_CUSTOMER_CONVERTED,
            description: "Lead '{$lead->title}' was converted to customer.",
            lead: $lead,
            customer: $customer,
            properties: ['customer_id' => $customer->id]
        );

        return response()->json([
            'message'  => 'Lead converted to customer successfully.',
            'customer' => $customer->load('lead'),
        ], 201);
    }

    /**
     * Show a customer with full profile.
     */
    public function show(Customer $customer): JsonResponse
    {
        return response()->json([
            'customer' => $customer->load([
                'lead:id,title,status',
                'assignee:id,name,email',
                'contacts',
                'notes.user:id,name',
                'tasks',
                'activities.user:id,name',
            ]),
        ]);
    }

    /**
     * Update customer profile.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        $customer->update($request->validated());

        return response()->json([
            'message'  => 'Customer updated successfully.',
            'customer' => $customer->load('assignee:id,name'),
        ]);
    }

    /**
     * Delete customer.
     */
    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        return response()->json(['message' => 'Customer deleted successfully.']);
    }

    // --- Contacts ---

    public function storeContact(Request $request, Customer $customer): JsonResponse
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'title'      => 'nullable|string|max:255',
            'email'      => 'nullable|email',
            'phone'      => 'nullable|string|max:20',
            'is_primary' => 'boolean',
        ]);

        if (!empty($data['is_primary'])) {
            $customer->contacts()->update(['is_primary' => false]);
        }

        $contact = $customer->contacts()->create($data);

        return response()->json(['message' => 'Contact added.', 'contact' => $contact], 201);
    }

    public function destroyContact(Customer $customer, $contactId): JsonResponse
    {
        $customer->contacts()->findOrFail($contactId)->delete();

        return response()->json(['message' => 'Contact removed.']);
    }

    // --- Notes ---

    public function storeNote(Request $request, Customer $customer): JsonResponse
    {
        $request->validate(['content' => 'required|string']);

        $note = $customer->notes()->create([
            'user_id' => $request->user()->id,
            'content' => $request->content,
        ]);

        $this->activityService->log(
            user: $request->user(),
            type: Activity::TYPE_NOTE_ADDED,
            description: 'A note was added to the customer.',
            customer: $customer,
        );

        return response()->json(['message' => 'Note added.', 'note' => $note->load('user:id,name')], 201);
    }
}
