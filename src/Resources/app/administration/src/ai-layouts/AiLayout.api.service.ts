export default class AiLayoutApiService extends Shopware.Classes.ApiService {
    static name = 'AiLayoutApiService';

    constructor(httpClient, loginService, apiEndpoint = '_action/ai-layout') {
        super(httpClient, loginService, apiEndpoint);
    }

    prompt(prompt: string, pageData: object) {
        const headers = this.getBasicHeaders({});

         return this.httpClient.post(
            `${this.apiEndpoint}/prompt`,
            { prompt, pageData },
            { headers }
        ).then((response) => {
            return Shopware.Classes.ApiService.handleResponse(response);
        });
    }
}
