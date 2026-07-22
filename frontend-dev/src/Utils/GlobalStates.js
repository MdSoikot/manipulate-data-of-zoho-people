import { atom } from 'jotai'

export const $integrationDetails = atom(Object.keys(bitwelzp.integration_details).length > 0 ? {

  integ_config: {
    integration_details: bitwelzp.integration_details,
    auth_details: JSON.parse(bitwelzp.auth_details),
  },
}
  : {
    integ_config: {
      auth_details: {
        clientId: '',
        clientSecret: '',
        dataCenter: '',
        tokenDetails: '',
        isAuthorized: false,
      },
    },
  })
