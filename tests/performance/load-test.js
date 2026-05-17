import http from 'k6/http';
import { check, sleep, group } from 'k6';

export const options = {
  stages: [
    { duration: '30s', target: 20 }, // ramp up to 20 users
    { duration: '1m', target: 20 },  // stay at 20 users
    { duration: '30s', target: 0 },  // ramp down
  ],
  thresholds: {
    http_req_duration: ['p(95)<1000'], // 95% of requests must complete below 1000ms (adjusted for CI overhead)
    http_req_failed: ['rate<0.01'],    // less than 1% of requests should fail
  },
};

export default function () {
  const BASE_URL = 'http://localhost:8000';

  group('Public Pages', function () {
    // Test the Home/Dashboard page
    const res = http.get(`${BASE_URL}`);
    check(res, {
      'status is 200': (r) => r.status === 200,
      'content is present': (r) => r.body.includes('Analyt Loan'),
    });
  });

  group('API Endpoints (Read-only)', function () {
    // Test a common API endpoint or a heavy read route
    // Since we seeded the DB, these should return data
    const endpoints = [
      '/up',         // Health check (Laravel 11 default)
      '/api/loans',  // Publicly accessible loan list (if no auth middleware)
    ];

    endpoints.forEach(path => {
      const res = http.get(`${BASE_URL}${path}`);
      check(res, {
        [`${path} status is acceptable`]: (r) => r.status === 200 || r.status === 401,
      });
    });
  });

  sleep(1);
}
