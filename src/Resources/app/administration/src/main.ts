import './ai-layouts/ai-layout-sidebar-item';
import './extension/sw-cms/component/sw-cms-sidebar';

import AiLayoutApiService from './ai-layouts/AiLayout.api.service';

Shopware.Application.addServiceProvider(AiLayoutApiService.name, (container) => {
    const initContainer = Shopware.Application.getContainer('init');
    return new AiLayoutApiService(initContainer.httpClient, container.loginService);
});
