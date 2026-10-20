import { createRoot } from 'react-dom/client';
import ProductCRUD from './components/ProductCRUD';

const container = document.getElementById('product-crud-root');
if (container) {
    createRoot(container).render(<ProductCRUD />);
}