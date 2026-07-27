import React from 'react';
import { ServerContext } from '@/state/server';
import ScreenBlock from '@/components/elements/ScreenBlock';
import ServerInstallSvg from '@/assets/images/server_installing.svg';
import ServerErrorSvg from '@/assets/images/server_error.svg';
import ServerRestoreSvg from '@/assets/images/server_restore.svg';

export default () => {
    const status = ServerContext.useStoreState((state) => state.server.data?.status || null);
    const expiresAt = ServerContext.useStoreState((state) => state.server.data?.expiresAt || null);
    const isTransferring = ServerContext.useStoreState((state) => state.server.data?.isTransferring || false);
    const isNodeUnderMaintenance = ServerContext.useStoreState(
        (state) => state.server.data?.isNodeUnderMaintenance || false
    );
    const isExpired = expiresAt ? new Date(expiresAt).getTime() <= Date.now() : false;

    return status === 'installing' || status === 'install_failed' || status === 'reinstall_failed' ? (
        <ScreenBlock
            title={'Running Installer'}
            image={ServerInstallSvg}
            message={'Your server should be ready soon, please try again in a few minutes.'}
        />
    ) : status === 'suspended' ? (
        <ScreenBlock
            title={isExpired ? 'Server Expired' : 'Server Suspended'}
            image={ServerErrorSvg}
            message={
                isExpired
                    ? 'Masa aktif server ini sudah expired. Silahkan melakukan perpanjangan untuk mengaktifkan kembali server.'
                    : 'This server is suspended and cannot be accessed.'
            }
        />
    ) : isNodeUnderMaintenance ? (
        <ScreenBlock
            title={'Node under Maintenance'}
            image={ServerErrorSvg}
            message={'The node of this server is currently under maintenance.'}
        />
    ) : (
        <ScreenBlock
            title={isTransferring ? 'Transferring' : 'Restoring from Backup'}
            image={ServerRestoreSvg}
            message={
                isTransferring
                    ? 'Your server is being transferred to a new node, please check back later.'
                    : 'Your server is currently being restored from a backup, please check back in a few minutes.'
            }
        />
    );
};
