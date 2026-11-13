import React, { useRef } from 'react';
import { motion, useInView } from 'framer-motion';

const defaultVariants = {
    hidden: {
        opacity: 0,
        y: 24,
        filter: 'blur(4px)',
    },
    visible: {
        opacity: 1,
        y: 0,
        filter: 'blur(0px)',
        transitionEnd: {
            filter: 'none',
        },
    },
};

const defaultTransition = {
    duration: 0.5,
    ease: [0.21, 0.47, 0.32, 0.98],
};

const defaultViewOptions = {
    once: true,
    margin: '0px 0px 50px 0px',
};

export default function InView({
    children,
    className = '',
    variants = defaultVariants,
    transition = defaultTransition,
    viewOptions = defaultViewOptions,
    as = 'div',
    delay = 0,
    style = {},
    initial = 'hidden',
}) {
    const ref = useRef(null);
    const isInView = useInView(ref, viewOptions);

    const MotionComponent = motion[as] || motion.div;
    const resolvedTransition = delay > 0 ? { ...transition, delay } : transition;
    const isVisible = isInView || initial === 'visible' || initial === false;

    return (
        <MotionComponent
            ref={ref}
            initial={initial}
            animate={isVisible ? 'visible' : 'hidden'}
            variants={variants}
            transition={resolvedTransition}
            style={{
                willChange: isVisible ? 'auto' : 'transform, opacity',
                ...style,
            }}
            className={className}
        >
            {children}
        </MotionComponent>
    );
}
