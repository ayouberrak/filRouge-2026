import api from './api';

const DailyReportService = {
    getStats(classroomId) {
        return api.get(`/reports/stats/${classroomId}`);
    },

    getByClassroom(classroomId) {
        return api.get(`/reports/classroom/${classroomId}`);
    },

    submitReport(data) {
        return api.post('/reports', data);
    }
};

export default DailyReportService;
