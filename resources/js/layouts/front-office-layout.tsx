import FrontOfficeTemplate from '@/layouts/front-office/front-office-layout';
import type { AppLayoutProps } from '@/types';

export default function FrontOfficeLayout(props: AppLayoutProps) {
    return <FrontOfficeTemplate {...props} />;
}
