import api from './api';

const AbsenceService = {
  /**
   * Get absences for a classroom by month
   * @param {number} classroomId 
   * @param {string} month YYYY-MM
   */
  getByClassroom(classroomId, month) {
    return api.get(`/absences/classroom/${classroomId}`, {
      params: { month }
    });
  },

  /**
   * Create or update an absence record
   * @param {Object} data { student_id, date, duration }
   */
  create(data) {
    return api.post('/absences/create', data);
  },

  /**
   * Delete an absence record
   * @param {number} id 
   */
  delete(id) {
    return api.delete(`/absences/${id}`);
  }
};

export default AbsenceService;
