import http from '@/api/http';

export interface RegisterData {
    username: string;
    email: string;
    name_first: string;
    name_last: string;
    password: string;
    password_confirmation: string;
}

export interface RegisterResponse {
    success: boolean;
    redirect: string;
}

export default (data: RegisterData): Promise<RegisterResponse> => {
    return new Promise((resolve, reject) => {
        http.get('/sanctum/csrf-cookie')
            .then(() => http.post('/auth/register', data))
            .then((response) => {
                if (!(response.data instanceof Object)) {
                    return reject(new Error('An error occurred while processing the registration.'));
                }

                return resolve({
                    success: response.data.success,
                    redirect: response.data.redirect,
                });
            })
            .catch(reject);
    });
};
