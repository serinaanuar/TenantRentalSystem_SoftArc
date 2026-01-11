import React, { useState, useEffect } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import MainLayout from '@/Layouts/MainLayout';
import axios from 'axios';

export default function OwnerMaintenancePage({ auth, maintenanceRequests: initialRequests, statusOptions }) {
    const [maintenanceRequests, setMaintenanceRequests] = useState(initialRequests || []);
    const [selectedRequest, setSelectedRequest] = useState(null);
    const [showUpdateModal, setShowUpdateModal] = useState(false);
    const [statistics, setStatistics] = useState(null);
    const { data, setData, put, processing, errors, reset } = useForm({
        status: '',
        notes: '',
    });

    // Real-time updates via WebSocket
    useEffect(() => {
        if (auth.user?.id && window.Echo) {
            // Subscribe to maintenance updates for all seller's properties
            const generalChannel = window.Echo.channel('maintenance.updates');
            
            generalChannel.listen('.maintenance.status.updated', (event) => {
                console.log('Maintenance update received:', event);
                
                // Check if this maintenance request belongs to seller's property
                const requestBelongsToSeller = maintenanceRequests.some(
                    request => request.id === event.request_id
                );

                if (requestBelongsToSeller) {
                    // Update the maintenance request in the list
                    setMaintenanceRequests(prevRequests => 
                        prevRequests.map(request => 
                            request.id === event.request_id 
                                ? { ...request, status: event.status, updated_at: event.updated_at }
                                : request
                        )
                    );

                    // Reload statistics
                    loadStatistics();
                }
            });

            // Also subscribe to property-specific channels
            maintenanceRequests.forEach(request => {
                if (request.property_id) {
                    const propertyChannel = window.Echo.private(`maintenance.property.${request.property_id}`);
                    propertyChannel.listen('.maintenance.status.updated', (event) => {
                        console.log('Property-specific update:', event);
                    });
                }
            });

            return () => {
                generalChannel.stopListening('.maintenance.status.updated');
                window.Echo.leave('maintenance.updates');
            };
        }
    }, [auth.user?.id, maintenanceRequests.length]);

    useEffect(() => {
        loadStatistics();
    }, []);

    const loadStatistics = async () => {
        try {
            const response = await axios.get(route('api.maintenance.statistics'));
            setStatistics(response.data);
        } catch (error) {
            console.error('Error loading statistics:', error);
        }
    };

    const loadMaintenanceRequests = async () => {
        try {
            const response = await axios.get(route('api.maintenance.seller-requests'));
            setMaintenanceRequests(response.data);
        } catch (error) {
            console.error('Error loading maintenance requests:', error);
        }
    };

    const handleUpdateStatus = (request) => {
        setSelectedRequest(request);
        setData({
            status: request.status,
            notes: request.notes || '',
        });
        setShowUpdateModal(true);
    };

    const handleSubmitUpdate = (e) => {
        e.preventDefault();
        
        if (!selectedRequest) return;

        put(route('seller.maintenance.update-status', selectedRequest.id), {
            onSuccess: () => {
                setShowUpdateModal(false);
                reset();
                setSelectedRequest(null);
                // Reload requests and statistics
                loadMaintenanceRequests();
                loadStatistics();
            },
            onError: (errors) => {
                console.error('Update errors:', errors);
            }
        });
    };

    const getStatusBadgeClass = (status) => {
        const statusClasses = {
            'REQUESTED': 'bg-yellow-100 text-yellow-800 border-yellow-300',
            'REVIEWED': 'bg-blue-100 text-blue-800 border-blue-300',
            'IN_PROGRESS': 'bg-purple-100 text-purple-800 border-purple-300',
            'COMPLETED': 'bg-green-100 text-green-800 border-green-300',
        };
        return statusClasses[status] || 'bg-gray-100 text-gray-800 border-gray-300';
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

    const filterRequestsByStatus = (status) => {
        return maintenanceRequests.filter(request => request.status === status);
    };

    return (
        <MainLayout auth={auth}>
            <Head title="Maintenance Management" />
            
            <div className="min-h-screen bg-gray-50 py-8">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Header */}
                    <div className="mb-8">
                        <h1 className="text-3xl font-bold text-gray-900">Maintenance Management</h1>
                        <p className="mt-2 text-sm text-gray-600">
                            Manage and update maintenance requests for your properties
                        </p>
                    </div>

                    {/* Statistics Cards */}
                    {statistics && (
                        <div className="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
                            <div className="bg-white rounded-lg shadow p-6">
                                <div className="text-sm font-medium text-gray-500 mb-1">Total</div>
                                <div className="text-3xl font-bold text-gray-900">{statistics.total}</div>
                            </div>
                            <div className="bg-yellow-50 rounded-lg shadow p-6 border-l-4 border-yellow-500">
                                <div className="text-sm font-medium text-yellow-700 mb-1">Requested</div>
                                <div className="text-3xl font-bold text-yellow-900">{statistics.requested}</div>
                            </div>
                            <div className="bg-blue-50 rounded-lg shadow p-6 border-l-4 border-blue-500">
                                <div className="text-sm font-medium text-blue-700 mb-1">Reviewed</div>
                                <div className="text-3xl font-bold text-blue-900">{statistics.reviewed}</div>
                            </div>
                            <div className="bg-purple-50 rounded-lg shadow p-6 border-l-4 border-purple-500">
                                <div className="text-sm font-medium text-purple-700 mb-1">In Progress</div>
                                <div className="text-3xl font-bold text-purple-900">{statistics.in_progress}</div>
                            </div>
                            <div className="bg-green-50 rounded-lg shadow p-6 border-l-4 border-green-500">
                                <div className="text-sm font-medium text-green-700 mb-1">Completed</div>
                                <div className="text-3xl font-bold text-green-900">{statistics.completed}</div>
                            </div>
                        </div>
                    )}

                    {/* Maintenance Requests List */}
                    <div className="bg-white rounded-lg shadow-md overflow-hidden">
                        <div className="px-6 py-4 border-b border-gray-200">
                            <h2 className="text-xl font-semibold">Maintenance Requests</h2>
                        </div>

                        {maintenanceRequests.length === 0 ? (
                            <div className="p-12 text-center">
                                <div className="text-gray-400 mb-4">
                                    <svg className="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 className="text-lg font-medium text-gray-900 mb-2">No maintenance requests</h3>
                                <p className="text-gray-500">There are no maintenance requests for your properties yet.</p>
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
                                                    Property: {request.property?.property_name} - {request.property?.property_address_line_1}
                                                </p>
                                                <p className="text-sm text-gray-600">
                                                    Submitted by: {request.user?.firstname} {request.user?.lastname}
                                                </p>
                                            </div>
                                            <div className="flex gap-2 flex-col items-end">
                                                <div className="flex gap-2">
                                                    <span className={`px-3 py-1 rounded-full text-xs font-medium border ${getStatusBadgeClass(request.status)}`}>
                                                        {request.status.replace('_', ' ')}
                                                    </span>
                                                    <span className={`px-3 py-1 rounded-full text-xs font-medium ${getPriorityBadgeClass(request.priority)}`}>
                                                        {request.priority}
                                                    </span>
                                                </div>
                                                <button
                                                    onClick={() => handleUpdateStatus(request)}
                                                    className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1 rounded text-sm font-medium transition-colors"
                                                >
                                                    Update Status
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div className="bg-gray-50 rounded-lg p-4 mb-3">
                                            <p className="text-sm font-medium text-gray-700 mb-1">Description:</p>
                                            <p className="text-gray-700">{request.description}</p>
                                        </div>
                                        
                                        {request.notes && (
                                            <div className="bg-blue-50 border-l-4 border-blue-400 p-3 mb-3">
                                                <p className="text-sm text-blue-900">
                                                    <strong>Your Notes:</strong> {request.notes}
                                                </p>
                                            </div>
                                        )}
                                        
                                        <div className="flex justify-between items-center text-sm text-gray-500">
                                            <span>Submitted: {formatDate(request.created_at)}</span>
                                            <span>Last Updated: {formatDate(request.updated_at)}</span>
                                        </div>

                                        {request.completed_at && (
                                            <div className="mt-2 text-sm text-green-600 font-medium">
                                                ✓ Completed: {formatDate(request.completed_at)}
                                            </div>
                                        )}
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Update Status Modal */}
            {showUpdateModal && selectedRequest && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                    <div className="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                        <div className="px-6 py-4 border-b border-gray-200">
                            <h2 className="text-2xl font-semibold">Update Maintenance Request</h2>
                        </div>

                        <div className="p-6">
                            {/* Request Details */}
                            <div className="mb-6 bg-gray-50 rounded-lg p-4">
                                <h3 className="font-semibold text-lg mb-2">{selectedRequest.title}</h3>
                                <p className="text-sm text-gray-600 mb-2">
                                    Property: {selectedRequest.property?.property_name}
                                </p>
                                <p className="text-sm text-gray-700">{selectedRequest.description}</p>
                            </div>

                            {/* Update Form */}
                            <form onSubmit={handleSubmitUpdate} className="space-y-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Status *
                                    </label>
                                    <select
                                        value={data.status}
                                        onChange={e => setData('status', e.target.value)}
                                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        required
                                    >
                                        {statusOptions.map(status => (
                                            <option key={status} value={status}>
                                                {status.replace('_', ' ')}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.status && (
                                        <p className="mt-1 text-sm text-red-600">{errors.status}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Notes (Optional)
                                    </label>
                                    <textarea
                                        value={data.notes}
                                        onChange={e => setData('notes', e.target.value)}
                                        rows="4"
                                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Add any notes or updates for the tenant..."
                                    />
                                    {errors.notes && (
                                        <p className="mt-1 text-sm text-red-600">{errors.notes}</p>
                                    )}
                                </div>

                                <div className="flex gap-4 pt-4">
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors disabled:opacity-50"
                                    >
                                        {processing ? 'Updating...' : 'Update Status'}
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setShowUpdateModal(false);
                                            setSelectedRequest(null);
                                            reset();
                                        }}
                                        className="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg font-medium transition-colors"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}
        </MainLayout>
    );
}
