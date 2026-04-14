import { Component, type ReactNode } from 'react';
import { Outlet } from 'react-router-dom';
import styles from './MainLayout.module.scss';
import { Header }  from '@/components/layout/Header';
import { Sidebar } from '@/components/layout/Sidebar';
import { Footer }  from '@/components/layout/Footer';
import { ErrorFallback } from '@/components/feedback/ErrorFallback';

interface ErrorBoundaryState {
  hasError: boolean;
  error:    Error | null;
}

class ErrorBoundary extends Component<
  { children: ReactNode },
  ErrorBoundaryState
> {
  state: ErrorBoundaryState = { hasError: false, error: null };

  static getDerivedStateFromError(error: Error): ErrorBoundaryState {
    return { hasError: true, error };
  }

  handleReset = () => {
    this.setState({ hasError: false, error: null });
  };

  render() {
    if (this.state.hasError) {
      return (
        <ErrorFallback error={this.state.error ?? undefined} onReset={this.handleReset} />
      );
    }
    return this.props.children;
  }
}

export function MainLayout() {
  return (
    <div className={styles.layout}>
      <Sidebar />
      <div className={styles.content}>
        <Header />
        <main className={styles.main}>
          <ErrorBoundary>
            <Outlet />
          </ErrorBoundary>
        </main>
        <Footer />
      </div>
    </div>
  );
}
