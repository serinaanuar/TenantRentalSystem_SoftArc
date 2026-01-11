import React, { useState, useEffect } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import MainLayout from '@/Layouts/MainLayout';
import axios from 'axios';

export default function MaintenancePage({ auth, maintenanceRequests: initialRequests, userProperties }) {
    const [maintenanceRequests, setMaintenanceRequests] = useState(initialRequests || []);
    const [showForm, setShowForm] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({
        property_id: '',
        title: '',
        description: '',
        priority: 'MEDIUM',
    });

    // Ensure CSRF token is set on component mount
    useEffect(() => {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (token) {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
        }
        axios.defaults.headers.common['Accept'] = 'application/json';
        axios.defaults.withCredentials = true;
    }, []);

    // Real-time updates via WebSocket
    useEffect(() => {
        if (auth.user?.id && window.Echo) {
            // Subscribe to user-specific maintenance updates
            const channel = window.Echo.private(`maintenance.user.${auth.user.id}`);
            
            channel.listen('.maintenance.status.updated', (event) => {
                console.log('Maintenance status updated:', event);
                
                // Update the maintenance request in the list
                setMaintenanceRequests(prevRequests => 
                    prevRequests.map(request => 
                        request.id === event.request_id 
                            ? { ...request, status: event.status, updated_at: event.updated_at }
                            : request
                    )
                );

                // Show notification to user
                alert(`Maintenance request status updated to: ${event.status}`);
            });

            // Also listen to general maintenance updates channel
            const generalChannel = window.Echo.channel('maintenance.updates');
            generalChannel.listen('.maintenance.status.updated', (event) => {
                if (event.user_id === auth.user.id) {
                    console.log('General maintenance update:', event);
                }
            });

            return () => {
                channel.stopListening('.maintenance.status.updated');
                window.Echo.leave(`maintenance.user.${auth.user.id}`);
                generalChannel.stopListening('.maintenance.status.updated');
                window.Echo.leave('maintenance.updates');
            };
        }
    }, [auth.user?.id]);

    const handleSubmit = (e) => {
        e.preventDefault();
        
        // Validate description
        if (!data.description || data.description.trim().length < 10) {
            alert('Description must be at least 10 characters long.');
            return;
        }

        // Ensure CSRF token is set
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (token) {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
        }

        post(route('maintenance.store'), {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                setShowForm(false);
                // Reload requests
                loadMaintenanceRequests();
            },
            onError: (errors) => {
                console.error('Submission errors:', errors);
                alert('Failed to submit request. Please refresh the page and try again.');
            }
        });
    };

    const loadMaintenanceRequests = async () => {
        try {
            const response = await axios.get(route('api.maintenance.user-requests'));
            setMaintenanceRequests(response.data);
        } catch (error) {
            console.error('Error loading maintenance requests:', error);
        }
    };

    const getStatusBadgeClass = (status) => {
        const statusClasses = {
            'REQUESTED': 'bg-yellow-100 text-yellow-800',
            'REVIEWED': 'bg-blue-100 text-blue-800',
            'IN_PROGRESS': 'bg-purple-100 text-purple-800',
            'COMPLETED': 'bg-green-100 text-green-800',
        };
        return statusClasses[status] || 'bg-gray-100 text-gray-800';
    };

    const getPriorityBadgeClass = (priority) => {
        const priorityClasses = {
            'LOW': 'bg-gray-100 text-gray-600',
            'MEDIUM': 'bg-blue-100 text-blue-600',
            'HIGH': 'bg-orange-100 text-orange-600',
            'URGENT': 'bg-red-100 text-red-600',
        };
        return priorityClasses[priority] || 'bg-gray-100 text-gray-600';
    };

    const formatDate = (dateString) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    return (
        <MainLayout auth={auth}>
            <Head title="Maintenance Requests" />
            
            <div className="min-h-screen bg-gray-50 py-8">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Header */}
                    <div className="mb-8">
                        <h1 className="text-3xl font-bold text-gray-900">Maintenance Requests</h1>
                        <p className="mt-2 text-sm text-gray-600">
                            Submit and track maintenance requests for your properties
                        </p>
                    </div>

                    {/* Request Form */}
                    {showForm && (
                        <div className="bg-white rounded-lg shadow-lg border border-gray-200 p-8 mb-8 animate-fadeIn">
                            <div className="flex items-center gap-3 mb-6">
                                <div className="bg-blue-100 p-3 rounded-lg">
                                    <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 className="text-2xl font-bold text-gray-900">Submit Maintenance Request</h2>
                                    <p className="text-sm text-gray-600">Fill in the details below to request maintenance</p>
                                </div>
                            </div>

                            {userProperties && userProperties.length === 0 ? (
                                <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                                    <svg className="w-12 h-12 text-yellow-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <h3 className="text-lg font-semibold text-gray-900 mb-2">No Properties Available</h3>
                                    <p className="text-gray-600 mb-4">
                                        You don't have any properties associated with your account yet. 
                                        To submit a maintenance request, you need to be associated with a property.
                                    </p>
                                    <button
                                        type="button"
                                        onClick={() => setShowForm(false)}
                                        className="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors"
                                    >
                                        Close
                                    </button>
                                </div>
                            ) : (
                            <form onSubmit={handleSubmit} className="space-y-6">
                                <div>
                                    <label className="block text-sm font-semibold text-gray-700 mb-2">
                                        Select Property <span className="text-red-500">*</span>
                                    </label>
                                    <div className="relative">
                                        <select
                                            value={data.property_id}
                                            onChange={e => setData('property_id', e.target.value)}
                                            className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none bg-white"
                                            required
                                        >
                                            <option value="">-- Choose a property --</option>
                                            {userProperties.map(property => (
                                                <option key={property.id} value={property.id}>
                                                    {property.property_name} - {property.property_address_line_1}, {property.city}
                                                </option>
                                            ))}
                                        </select>
                                        <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
                                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </div>
                                    {errors.property_id && (
                                        <p className="mt-2 text-sm text-red-600 flex items-center gap-1">
                                            <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                                            </svg>
                                            {errors.property_id}
                                        </p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-sm font-semibold text-gray-700 mb-2">
                                        Issue Title <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        value={data.title}
                                        onChange={e => setData('title', e.target.value)}
                                        className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="e.g., Leaking faucet in kitchen"
                                        required
                                    />
                                    {errors.title && (
                                        <p className="mt-2 text-sm text-red-600 flex items-center gap-1">
                                            <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                                            </svg>
                                            {errors.title}
                                        </p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-sm font-semibold text-gray-700 mb-2">
                                        Description <span className="text-red-500">*</span>
                                    </label>
                                    <textarea
                                        value={data.description}
                                        onChange={e => setData('description', e.target.value)}
                                        rows="5"
                                        className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Please describe the maintenance issue in detail (minimum 10 characters)..."
                                        required
                                        minLength="10"
                                    />
                                    <div className="mt-1 flex justify-between items-center">
                                        <p className="text-xs text-gray-500">
                                            Minimum 10 characters
                                        </p>
                                        <p className={`text-xs ${data.description.length >= 10 ? 'text-green-600' : 'text-gray-400'}`}>
                                            {data.description.length} characters
                                        </p>
                                    </div>
                                    {errors.description && (
                                        <p className="mt-2 text-sm text-red-600 flex items-center gap-1">
                                            <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                                            </svg>
                                            {errors.description}
                                        </p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-sm font-semibold text-gray-700 mb-2">
                                        Priority Level
                                    </label>
                                    <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                                        {[
                                            { value: 'LOW', label: 'Low', color: 'gray', icon: '📋' },
                                            { value: 'MEDIUM', label: 'Medium', color: 'blue', icon: '📌' },
                                            { value: 'HIGH', label: 'High', color: 'orange', icon: '⚠️' },
                                            { value: 'URGENT', label: 'Urgent', color: 'red', icon: '🚨' }
                                        ].map(priority => (
                                            <button
                                                key={priority.value}
                                                type="button"
                                                onClick={() => setData('priority', priority.value)}
                                                className={`px-4 py-3 rounded-lg border-2 font-medium transition-all ${
                                                    data.priority === priority.value
                                                        ? `border-${priority.color}-500 bg-${priority.color}-50 text-${priority.color}-700 shadow-md`
                                                        : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300'
                                                }`}
                                            >
                                                <span className="text-xl mr-2">{priority.icon}</span>
                                                {priority.label}
                                            </button>
                                        ))}
                                    </div>
                                    <p className="mt-2 text-xs text-gray-500">
                                        Select the urgency level of this maintenance request
                                    </p>
                                </div>

                                <div className="flex gap-4 pt-4 border-t">
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                                    >
                                        {processing ? (
                                            <>
                                                <svg className="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Submitting...
                                            </>
                                        ) : (
                                            <>
                                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Submit Request
                                            </>
                                        )}
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setShowForm(false);
                                            reset();
                                        }}
                                        className="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-semibold transition-colors"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </form>
                            )}
                        </div>
                    )}

                    {/* Maintenance Requests List */}
                    <div className="bg-white rounded-lg shadow-md overflow-hidden">
                        <div className="px-6 py-4 border-b border-gray-200">
                            <h2 className="text-xl font-semibold">Your Maintenance Requests</h2>
                        </div>

                        {maintenanceRequests.length === 0 ? (
                            <div className="p-12 text-center">
                                <div className="text-gray-400 mb-4">
                                    <svg className="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 className="text-lg font-medium text-gray-900 mb-2">No maintenance requests</h3>
                                <p className="text-gray-500">You haven't submitted any maintenance requests yet.</p>
                            </div>
                        ) : (
                            <div className="divide-y divide-gray-200">
                                {maintenanceRequests.map((request) => (
                                    <div key={request.id} className="p-6 hover:bg-gray-50 transition-colors">
                                        <div className="flex justify-between items-start mb-3">
                                            <div className="flex-1">
                                                <h3 className="text-lg font-semibold text-gray-900 mb-1">
                                                    {request.title}
                                                </h3>
                                                <p className="text-sm text-gray-600">
                                                    {request.property?.property_name} - {request.property?.property_address_line_1}
                                                </p>
                                            </div>
                                            <div className="flex gap-2">
                                                <span className={`px-3 py-1 rounded-full text-xs font-medium ${getStatusBadgeClass(request.status)}`}>
                                                    {request.status.replace('_', ' ')}
                                                </span>
                                                <span className={`px-3 py-1 rounded-full text-xs font-medium ${getPriorityBadgeClass(request.priority)}`}>
                                                    {request.priority}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <p className="text-gray-700 mb-3">{request.description}</p>
                                        
                                        {request.notes && (
                                            <div className="bg-blue-50 border-l-4 border-blue-400 p-3 mb-3">
                                                <p className="text-sm text-blue-900">
                                                    <strong>Seller Notes:</strong> {request.notes}
                                                </p>
                                            </div>
                                        )}
                                        
                                        <div className="flex justify-between items-center text-sm text-gray-500">
                                            <span>Submitted: {formatDate(request.created_at)}</span>
                                            <span>Last Updated: {formatDate(request.updated_at)}</span>
                                        </div>

                                        {request.completed_at && (
                                            <div className="mt-2 text-sm text-green-600">
                                                ✓ Completed: {formatDate(request.completed_at)}
                                            </div>
                                        )}
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Floating Action Button - Lower Right */}
                    <button
                        onClick={() => setShowForm(!showForm)}
                        className="fixed bottom-8 right-8 z-50 flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-4 rounded-full font-medium shadow-2xl hover:shadow-3xl transition-all transform hover:scale-110 group"
                        title={showForm ? "Cancel" : "Create New Request"}
                    >
                        {showForm ? (
                            <>
                                <svg className="w-6 h-6 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span className="hidden sm:inline">Cancel</span>
                            </>
                        ) : (
                            <>
                                <svg className="w-6 h-6 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span className="hidden sm:inline">Create New Request</span>
                            </>
                        )}
                    </button>
                </div>
            </div>
        </MainLayout>
    );
}
