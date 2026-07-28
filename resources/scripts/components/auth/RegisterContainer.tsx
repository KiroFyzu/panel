import React from 'react';
import { RouteComponentProps } from 'react-router-dom';
import register from '@/api/auth/register';
import LoginFormContainer from '@/components/auth/LoginFormContainer';
import { Formik, FormikHelpers } from 'formik';
import { object, string } from 'yup';
import Field from '@/components/elements/Field';
import tw from 'twin.macro';
import Button from '@/components/elements/Button';
import useFlash from '@/plugins/useFlash';

interface Values {
    username: string;
    email: string;
    name_first: string;
    name_last: string;
    password: string;
    password_confirmation: string;
}

const RegisterContainer = ({ history }: RouteComponentProps) => {
    const { clearFlashes, clearAndAddHttpError } = useFlash();

    const onSubmit = (values: Values, { setSubmitting }: FormikHelpers<Values>) => {
        clearFlashes();

        register(values)
            .then((response) => {
                // @ts-expect-error this is valid
                window.location = response.redirect || '/';
            })
            .catch((error) => {
                console.error(error);
                setSubmitting(false);
                clearAndAddHttpError({ error });
            });
    };

    return (
        <Formik
            onSubmit={onSubmit}
            initialValues={{
                username: '',
                email: '',
                name_first: '',
                name_last: '',
                password: '',
                password_confirmation: '',
            }}
            validationSchema={object().shape({
                username: string()
                    .required('Username harus diisi.')
                    .min(1)
                    .max(191)
                    .matches(/^[a-z0-9]([\w.-]+)[a-z0-9]$/, 'Username hanya boleh huruf kecil, angka, -, _, dan .'),
                email: string().required('Email harus diisi.').email('Format email tidak valid.'),
                name_first: string().required('Nama depan harus diisi.').max(191),
                name_last: string().required('Nama belakang harus diisi.').max(191),
                password: string()
                    .required('Password harus diisi.')
                    .min(8, 'Password minimal 8 karakter.'),
                password_confirmation: string()
                    .required('Konfirmasi password harus diisi.')
                    .oneOf([], 'Password tidak cocok.'),
            })}
        >
            {({ isSubmitting }) => (
                <LoginFormContainer title={'Buat Akun Baru'}>
                    <Field light type={'text'} label={'Username'} name={'username'} disabled={isSubmitting} />
                    <div css={tw`mt-6`}>
                        <Field light type={'email'} label={'Email'} name={'email'} disabled={isSubmitting} />
                    </div>
                    <div css={tw`mt-6 flex gap-4`}>
                        <div css={tw`flex-1`}>
                            <Field light type={'text'} label={'Nama Depan'} name={'name_first'} disabled={isSubmitting} />
                        </div>
                        <div css={tw`flex-1`}>
                            <Field light type={'text'} label={'Nama Belakang'} name={'name_last'} disabled={isSubmitting} />
                        </div>
                    </div>
                    <div css={tw`mt-6`}>
                        <Field light type={'password'} label={'Password'} name={'password'} disabled={isSubmitting} />
                    </div>
                    <div css={tw`mt-6`}>
                        <Field
                            light
                            type={'password'}
                            label={'Konfirmasi Password'}
                            name={'password_confirmation'}
                            disabled={isSubmitting}
                        />
                    </div>
                    <div css={tw`mt-6`}>
                        <Button type={'submit'} size={'xlarge'} isLoading={isSubmitting} disabled={isSubmitting}>
                            Daftar
                        </Button>
                    </div>
                </LoginFormContainer>
            )}
        </Formik>
    );
};

export default RegisterContainer;
