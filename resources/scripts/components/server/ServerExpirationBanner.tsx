import React from 'react';
import { formatDistanceToNowStrict, parseISO } from 'date-fns';
import tw from 'twin.macro';
import ContentContainer from '@/components/elements/ContentContainer';
import MessageBox from '@/components/MessageBox';
import { ServerContext } from '@/state/server';

const WARNING_WINDOW_MS = 7 * 24 * 60 * 60 * 1000;

export default () => {
    const expiresAt = ServerContext.useStoreState((state) => state.server.data?.expiresAt);

    if (!expiresAt) {
        return null;
    }

    const date = parseISO(expiresAt);
    const remaining = date.getTime() - Date.now();

    if (Number.isNaN(remaining) || remaining > WARNING_WINDOW_MS) {
        return null;
    }

    const expired = remaining <= 0;

    return (
        <ContentContainer css={tw`mt-4`}>
            <MessageBox type={expired ? 'error' : 'warning'} title={expired ? 'Expired' : 'Expiring Soon'}>
                {expired
                    ? 'This server has expired and may be suspended automatically soon.'
                    : `This server expires in ${formatDistanceToNowStrict(date)}.`}
            </MessageBox>
        </ContentContainer>
    );
};
