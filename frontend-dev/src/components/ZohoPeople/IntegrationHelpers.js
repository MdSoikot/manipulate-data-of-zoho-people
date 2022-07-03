export const setGrantTokenResponse = (integ) => {
    const grantTokenResponse = {}
    const authWindowLocation = window.location.href
    const queryParams = authWindowLocation.replace(`${window.opener.location.href}/redirect`, '').split('&')
    if (queryParams) {
        queryParams.forEach(element => {
            const gtKeyValue = element.split('=')
            if (gtKeyValue[1]) {
                // eslint-disable-next-line prefer-destructuring
                grantTokenResponse[gtKeyValue[0]] = gtKeyValue[1]
            }
        })
    }
    localStorage.setItem(`${integ}`, JSON.stringify(grantTokenResponse))
    window.close()
}