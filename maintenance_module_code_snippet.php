public interface MaintenanceSubject {
    void attach(MaintenanceObserver observer);
    void detach(MaintenanceObserver observer);
    void notifyObservers();
}

public interface MaintenanceObserver {
    void update(String status);
}

public class MaintenancePage implements MaintenanceObserver {

    @Override
    public void update(String status) {
        System.out.println(
            "User notification: Maintenance status updated to " + status
        );
    }
}

public class MaintenanceRequest implements MaintenanceSubject { 
		
		private String status;
		private int requestId;
		private String details;
		
		@Override

		public maintenanceRequest(String status, int requestId, String details) {
			this.status = status;
			this.requestId = requestId;
			this.details = details; }

		public void attach (MaintenanceObserver observer) {
observers.add(observer); }

public void detach (MaintenanceObserver observer) {
			observers.remove(observer); }

		public void notifyObservers () {
			for (MaintenanceObserver observers : observer) 	
			observer.update(status); }
		
		public List<MaintenanceRequest> retrieveRequests () {
			List<MaintenanceRequest maintenanceRequests = new ArrayList<>();
			return maintenanceRequests; }

		public void setStatus (String status) {
			this.status = status;
			notifyObservers(); }
}