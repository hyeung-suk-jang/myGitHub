import { RouterProvider } from 'react-router-dom';
import { router } from '@/routes';
import { ErrorToast } from '@/components/feedback/ErrorToast';

function App() {
  return (
    <>
      <RouterProvider router={router} />
      <ErrorToast />
    </>
  );
}

export default App;
